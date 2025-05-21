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

**Exigences strictes** :
1. Notation :
- La note totale de l'examen doit toujours être sur 20 points, quel que soit le nombre de questions.
- Répartissez équitablement les points entre les questions en fonction de leur difficulté :
  - Questions simples : 2-3 points
  - Questions moyennes : 4-6 points
  - Questions complexes : 7-10 points
- Le total des points doit exactement faire 20.

2. Structure des questions :
- Pour les questions à choix multiples (type "radio") :
  - 4 options dont une seule correcte
  - Indiquez clairement la réponse correcte
  - Points : 2-3 par question
  
- Pour les questions ouvertes (type "essay") :
  - Fournissez une réponse modèle détaillée
  - Points : 5-10 selon la complexité

3. Qualité :
- Les questions doivent couvrir les points clés du document
- Évitez les questions trop simples ou ambiguës
- Variez les types de questions

**Exemple de structure attendue** :
[
    {
        "text": "Question complexe sur un concept clé",
        "type": "essay",
        "points": 8,
        "correctAnswer": "Réponse détaillée attendue..."
    },
    {
        "text": "Question moyenne à choix multiple",
        "type": "radio",
        "points": 4,
        "options": [
            {"text": "Option partiellement correcte", "value": "A"},
            {"text": "Option correcte", "value": "B", "correct": true},
            {"text": "Option incorrecte", "value": "C"},
            {"text": "Option plausible mais fausse", "value": "D"}
        ]
    }
]

Générez maintenant l'examen en respectant strictement ces consignes.
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

    $allEmpty = true;
    foreach ($answers as $answer) {
        if (!empty($answer)) {
            $allEmpty = false;
            break;
        }
    }

    if ($allEmpty) {
        $this->logger->info('Aucune réponse fournie par l\'étudiant, retourne 0/20');
        return [
            'total_score' => 0,
            'questions' => array_map(function($q) {
                return [
                    'score' => 0,
                    'feedback' => 'Aucune réponse fournie'
                ];
            }, $questions)
        ];
    }

        $prompt = <<<EOD
Vous êtes un correcteur d'examen académique strict mais équitable. Votre tâche est d'évaluer les réponses étudiantes selon ces règles :

**Règles de correction** :
1. Notation :
- Une question sans réponse reçoit automatiquement 0
- Une réponse partiellement correcte reçoit entre 30% et 70% des points
- Une réponse complètement fausse mais pertinente reçoit entre 10% et 20% des points
- Une réponse hors sujet ou absurde reçoit 0

2. Feedback :
- Pour chaque réponse, fournissez un commentaire constructif
- Indiquez ce qui manque dans les réponses partielles
- Proposez des pistes d'amélioration

3. Équité :
- Soyez indulgent avec les réponses approximatives mais montrant un effort
- Soyez strict avec les réponses manifestement incorrectes ou non sérieuses

**Document de référence** :
{$pdfContent}

**Questions et réponses étudiantes** :
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

**Consignes finales** :
1. Analysez chaque réponse avec rigueur mais bienveillance
2. Attribuez les points selon le barème fourni
3. Fournissez un feedback détaillé pour chaque question
4. La note finale doit refléter exactement la performance réelle de l'étudiant
5. Si l'étudiant n'a répondu à aucune question, la note doit être 0/20

**Format de sortie attendu** :
{
    "total_score": [note sur 20],
    "questions": {
        "1": {
            "score": [points obtenus],
            "feedback": "Commentaire détaillé..."
        },
        // ...
    }
}

Procédez maintenant à la correction en appliquant strictement ces règles.
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