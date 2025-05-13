<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private $geminiApiKey;
    private $logger;
    private $httpClient;

    public function __construct(
        string $geminiApiKey,
        LoggerInterface $logger,
        HttpClientInterface $httpClient
    ) {
        $this->geminiApiKey = $geminiApiKey;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function generateExamQuestions(string $pdfContent, string $instructions): array
    {
        $this->logger->info('Génération des questions d\'examen avec Gemini', [
            'instructions' => $instructions,
            'pdf_content_length' => strlen($pdfContent)
        ]);

        $fixedInstructions = "À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.";
        $fullInstructions = $fixedInstructions . "\n" . $instructions;

        $prompt = <<<EOD
Vous êtes un assistant spécialisé dans la création d'examens académiques. Votre tâche est de générer des questions d'examen basées sur le contenu suivant et de suivre strictement les instructions fournies.

**Contenu du document** :
{$pdfContent}

**Instructions** :
{$fullInstructions}

**Exigences** :
- Générez les questions exactement comme demandé dans les instructions (nombre, type, niveau de difficulté, etc.).
- Si les instructions ne spécifient pas le nombre ou le type de questions, générez par défaut 5 questions : 3 à choix multiples (avec 4 options chacune, indiquer la réponse correcte) et 2 questions ouvertes.
- Chaque question doit inclure :
  - Le texte de la question.
  - Le type ("radio" pour choix multiples, "essay" pour ouvertes).
  - Les points (par défaut 1 point par question, sauf si spécifié).
  - Pour les questions à choix multiples, fournissez 4 options et indiquez la réponse correcte.
  - Pour les questions ouvertes, fournissez la réponse exacte attendue.
- Retournez les questions dans un format JSON structuré.

**Exemple de réponse** :
[
    {
        "text": "Quelle est la capitale de la France ?",
        "type": "radio",
        "points": 1,
        "options": [
            {"text": "Paris", "value": "A"},
            {"text": "Lyon", "value": "B"},
            {"text": "Marseille", "value": "C"},
            {"text": "Toulouse", "value": "D"}
        ],
        "correctAnswer": "A"
    },
    {
        "text": "Expliquez le rôle de la Révolution française.",
        "type": "essay",
        "points": 2,
        "correctAnswer": "La Révolution française (1789-1799) a renversé la monarchie absolue, établi des principes de liberté, égalité et fraternité, et marqué le début de la modernité politique en France."
    }
]

Générez les questions maintenant.
EOD;

        $maxRetries = 2;
        $attempt = 0;
        $questions = [];

        while ($attempt < $maxRetries) {
            try {
                $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->geminiApiKey, [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]
                ]);

                $data = $response->toArray();
                $this->logger->info('Réponse reçue de Gemini', ['response' => $data]);

                $generatedContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (empty($generatedContent)) {
                    $this->logger->error('Aucun contenu généré par Gemini');
                    throw new \Exception('Aucune question générée par Gemini');
                }

                $generatedContent = trim($generatedContent);
                $generatedContent = preg_replace('/^```json\n|\n```$/', '', $generatedContent);
                $questions = json_decode($generatedContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->logger->error('Erreur de parsing JSON', ['error' => json_last_error_msg(), 'content' => $generatedContent]);
                    throw new \Exception('Erreur lors du parsing des questions générées : ' . json_last_error_msg());
                }

                if (!is_array($questions)) {
                    $this->logger->error('Réponse invalide, tableau attendu', ['questions' => $questions]);
                    throw new \Exception('Réponse invalide de Gemini : tableau de questions attendu');
                }

                foreach ($questions as $index => &$question) {
                    if (!isset($question['text']) || !isset($question['type'])) {
                        $this->logger->warning('Question invalide', ['index' => $index, 'question' => $question]);
                        unset($questions[$index]);
                        continue;
                    }

                    $question['points'] = $question['points'] ?? 1;
                    if ($question['type'] === 'radio') {
                        if (!isset($question['options']) || count($question['options']) !== 4 || !isset($question['correctAnswer'])) {
                            $this->logger->warning('Question à choix multiples invalide', ['index' => $index, 'question' => $question]);
                            unset($questions[$index]);
                            continue;
                        }
                    } elseif ($question['type'] === 'essay' && !isset($question['correctAnswer'])) {
                        $this->logger->warning('Question ouverte sans réponse correcte', ['index' => $index, 'question' => $question]);
                        unset($questions[$index]);
                        continue;
                    }
                }

                $questions = array_values($questions);

                $expectedCount = $this->extractExpectedQuestionCount($instructions);
                if ($expectedCount && count($questions) !== $expectedCount) {
                    $this->logger->warning('Nombre de questions incorrect', [
                        'attendu' => $expectedCount,
                        'reçu' => count($questions),
                        'tentative' => $attempt + 1
                    ]);
                    $attempt++;
                    continue;
                }

                $expectedTypes = $this->extractExpectedQuestionTypes($instructions);
                if ($expectedTypes) {
                    $actualTypes = array_count_values(array_column($questions, 'type'));
                    foreach ($expectedTypes as $type => $count) {
                        if (($actualTypes[$type] ?? 0) !== $count) {
                            $this->logger->warning('Types de questions incorrects', [
                                'type' => $type,
                                'attendu' => $count,
                                'reçu' => $actualTypes[$type] ?? 0,
                                'tentative' => $attempt + 1
                            ]);
                            $attempt++;
                            continue 2;
                        }
                    }
                }

                $this->logger->info('Questions validées', ['question_count' => count($questions)]);
                return $questions;
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la génération des questions', [
                    'exception' => $e->getMessage(),
                    'tentative' => $attempt + 1
                ]);
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw new \Exception('Erreur lors de la génération des questions après plusieurs tentatives : ' . $e->getMessage());
                }
            }
        }

        throw new \Exception('Impossible de générer des questions conformes après ' . $maxRetries . ' tentatives');
    }

    public function analyzePdfErrors(string $pdfContent): array
    {
        $this->logger->info('Analyse des erreurs dans le PDF', [
            'pdf_content_length' => strlen($pdfContent)
        ]);

        $fixedInstructions = "À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.";

        $prompt = <<<EOD
Vous êtes un assistant spécialisé dans l'analyse de documents d'examen académique. Votre tâche est d'examiner le contenu d'un PDF pour identifier les erreurs potentielles qui pourraient affecter l'extraction de questions d'examen, en suivant les instructions fournies.

**Contenu du document** :
{$pdfContent}

**Instructions** :
{$fixedInstructions}

**Exigences** :
- Identifiez les erreurs telles que :
  - Absence de questions structurées (par exemple, le document ne contient pas de questions claires avec options ou réponses).
  - Questions à choix multiples sans 4 options ou sans réponse correcte.
  - Questions sans points attribués.
  - Questions ouvertes sans réponse exacte fournie.
  - Texte illisible ou mal structuré (par exemple, texte extrait comme du bruit ou des caractères incohérents).
  - Contenu insuffisant pour un examen (par exemple, trop court ou hors sujet).
  - Incohérences dans le formatage (par exemple, numérotation des questions manquante ou incorrecte).
- Retournez une liste JSON de dictionnaires, chaque dictionnaire contenant :
  - "error": Description de l'erreur en français.
  - "critical": Booléen indiquant si l'erreur empêche l'envoi (true pour l'absence de questions structurées, false sinon).
- Si aucune erreur n'est détectée, retournez une liste vide.
- Ne générez pas de questions, concentrez-vous uniquement sur l'analyse des erreurs.

**Exemple de réponse** :
[
    {
        "error": "Le document ne contient pas de questions structurées.",
        "critical": true
    },
    {
        "error": "Certaines questions à choix multiples n'ont pas 4 options.",
        "critical": false
    },
    {
        "error": "Aucune réponse correcte spécifiée pour la question 3.",
        "critical": false
    }
]

Analysez le contenu maintenant et retournez la liste des erreurs.
EOD;

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->geminiApiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = $response->toArray();
            $this->logger->info('Réponse reçue de Gemini pour analyse des erreurs', ['response' => $data]);

            $generatedContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (empty($generatedContent)) {
                $this->logger->error('Aucun contenu généré par Gemini pour analyse des erreurs');
                return [['error' => 'Aucun contenu analysé par l\'IA', 'critical' => false]];
            }

            $generatedContent = trim($generatedContent);
            $generatedContent = preg_replace('/^```json\n|\n```$/', '', $generatedContent);
            $errors = json_decode($generatedContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Erreur de parsing JSON pour analyse des erreurs', ['error' => json_last_error_msg(), 'content' => $generatedContent]);
                return [['error' => 'Erreur lors du parsing de l\'analyse des erreurs', 'critical' => false]];
            }

            if (!is_array($errors)) {
                $this->logger->error('Réponse invalide pour analyse des erreurs, tableau attendu', ['errors' => $errors]);
                return [['error' => 'Réponse invalide de l\'IA pour l\'analyse des erreurs', 'critical' => false]];
            }

            $this->logger->info('Erreurs analysées', ['error_count' => count($errors)]);
            return $errors;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'analyse des erreurs du PDF', ['exception' => $e->getMessage()]);
            return [['error' => 'Erreur lors de l\'analyse du PDF : ' . $e->getMessage(), 'critical' => false]];
        }
    }

    private function extractExpectedQuestionCount(string $instructions): ?int
    {
        if (preg_match('/(?:générer|créer)\s+(\d+)\s+questions?/i', $instructions, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    private function extractExpectedQuestionTypes(string $instructions): ?array
    {
        $types = [];
        if (preg_match('/(\d+)\s*(?:à\s*choix\s*multiple|radio)/i', $instructions, $matches)) {
            $types['radio'] = (int) $matches[1];
        }
        if (preg_match('/(\d+)\s*(?:ouvertes?|essay)/i', $instructions, $matches)) {
            $types['essay'] = (int) $matches[1];
        }
        return !empty($types) ? $types : null;
    }

    public function correctExam(array $answers, array $questions, string $pdfContent): array
    {
        $this->logger->info('Correction d\'examen avec Gemini', ['answer_count' => count($answers)]);

        $prompt = <<<EOD
Vous êtes un correcteur d'examen académique. Votre tâche est de corriger les réponses des étudiants en fonction des questions et du contenu du document fourni. Suivez ces instructions :

**Contenu du document** :
{$pdfContent}

**Questions et réponses** :
EOD;

        foreach ($questions as $index => $question) {
            $questionId = $question->getId();
            $answer = $answers[$questionId] ?? '';
            $prompt .= "\n**Question " . ($index + 1) . "** : " . $question->getTexte();
            if ($question->getType() === 'radio') {
                $prompt .= "\nOptions : ";
                foreach ($question->getOptions() as $option) {
                    $prompt .= "\n- " . $option->getTexte() . " (" . $option->getValeur() . ")";
                }
                $prompt .= "\nRéponse correcte : " . $question->getReponseCorrecte();
            } elseif ($question->getType() === 'essay') {
                $prompt .= "\nRéponse correcte : " . ($question->getReponseCorrecte() ?? 'Non fournie');
            }
            $prompt .= "\nRéponse de l'étudiant : " . json_encode($answer);
            $prompt .= "\nPoints : " . $question->getPoints();
        }

        $prompt .= <<<EOD

**Exigences** :
- Évaluez chaque réponse en fonction de sa pertinence et de son exactitude par rapport à la question et au contenu du document.
- Attribuez un score pour chaque question (maximum : points indiqués).
- Fournissez un commentaire expliquant la correction pour chaque question.
- Calculez un score total sur 20.
- Retournez un JSON avec :
  - total_score : Score total sur 20.
  - questions : Détails pour chaque question (question_id, score, feedback).

**Exemple de réponse** :
{
    "total_score": 15,
    "questions": {
        "1": {
            "score": 1,
            "feedback": "Réponse correcte."
        },
        "2": {
            "score": 1.5,
            "feedback": "Réponse partiellement correcte, manque de détails."
        }
    }
}
EOD;

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->geminiApiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = $response->toArray();
            $generatedContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $generatedContent = preg_replace('/^```json\n|\n```$/', '', $generatedContent);
            $correction = json_decode($generatedContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Erreur de parsing JSON pour la correction', ['error' => json_last_error_msg()]);
                throw new \Exception('Erreur lors du parsing de la correction : ' . json_last_error_msg());
            }

            $this->logger->info('Correction générée', ['total_score' => $correction['total_score']]);
            return $correction;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la correction', ['exception' => $e->getMessage()]);
            throw new \Exception('Erreur lors de la correction de l\'examen : ' . $e->getMessage());
        }
    }
}