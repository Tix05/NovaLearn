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

        $fixedInstructions = "À chaque question, précisez les réponses correctes (une ou plusieurs pour les questions de type radio, une réponse détaillée pour les questions ouvertes). Attribuez des points à chaque question en utilisant des valeurs décimales (par exemple, 0.25, 0.5). Le total des points doit EXACTEMENT égaler 20, même s'il y a un grand nombre de questions (jusqu'à 40).";
        $fullInstructions = $fixedInstructions . "\n" . $instructions;

        $prompt = <<<EOD
Vous êtes un assistant spécialisé dans la création d'examens académiques. Votre tâche est de générer des questions d'examen basées sur le contenu suivant et de suivre strictement les instructions fournies.

**Contenu du document** :
{$pdfContent}

**Instructions** :
{$fullInstructions}

**Exigences strictes** :
1. Notation :
- La note totale de l'examen doit EXACTEMENT être de 20 points, quel que soit le nombre de questions (de 1 à 40 questions possibles).
- Utilisez des points décimaux (par exemple, 0.25, 0.5, 1.0) pour répartir les points.
- Répartissez les points en fonction de la difficulté :
  - Questions simples : 0.25 à 1.0 point
  - Questions moyennes : 1.0 à 2.0 points
  - Questions complexes : 2.0 à 5.0 points
- Ajustez les points pour que le total soit exactement 20, même pour un grand nombre de questions.

2. Structure des questions :
- Pour les questions à choix multiples (type "radio") :
  - Générer entre 3 et 6 options par question, avec un nombre variable décidé par vous pour chaque question (pas toutes les questions avec le même nombre d'options).
  - Permettre de 1 à (n-1) réponses correctes, où n est le nombre d'options, le nombre exact étant décidé par vous.
  - Éviter les doublons dans les réponses correctes (chaque réponse correcte doit être unique).
  - Indiquer clairement toutes les réponses correctes dans "correctAnswers" (tableau de valeurs).
- Pour les questions ouvertes (type "essay") :
  - Fournir une réponse modèle détaillée dans "correctAnswer".
  - Points : 1.0 à 5.0 selon la complexité.

3. Qualité :
- Les questions doivent couvrir les points clés du document.
- Évitez les questions trop simples, ambiguës ou répétitives.
- Variez les types de questions (radio et essay) et le nombre d'options pour les questions radio.

**Exemple de structure attendue** :
[
    {
        "text": "Question complexe sur un concept clé",
        "type": "essay",
        "points": 3.5,
        "correctAnswer": "Réponse détaillée attendue..."
    },
    {
        "text": "Question à choix multiple",
        "type": "radio",
        "points": 1.25,
        "options": [
            {"text": "Option 1", "value": "A"},
            {"text": "Option 2", "value": "B"},
            {"text": "Option 3", "value": "C"},
            {"text": "Option 4", "value": "D"}
        ],
        "correctAnswers": ["B", "C"]
    }
]

Générez maintenant l'examen en respectant strictement ces consignes.
EOD;

        $maxRetries = 2;
        $attempt = 0;
        $questions = [];

        while ($attempt < $maxRetries) {
            try {
                $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->geminiApiKey, [
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
                    if (!isset($question['text']) || !isset($question['type']) || !isset($question['points'])) {
                        $this->logger->warning('Question invalide', ['index' => $index, 'question' => $question]);
                        unset($questions[$index]);
                        continue;
                    }

                    $question['points'] = (float) $question['points'];

                    if ($question['type'] === 'radio') {
                        if (!isset($question['options']) || count($question['options']) < 3 || count($question['options']) > 6 || !isset($question['correctAnswers'])) {
                            $this->logger->warning('Question à choix multiples invalide', ['index' => $index, 'question' => $question]);
                            unset($questions[$index]);
                            continue;
                        }
                        $numOptions = count($question['options']);
                        $correctAnswers = $question['correctAnswers'];
                        if (!is_array($correctAnswers) || count($correctAnswers) < 1 || count($correctAnswers) >= $numOptions) {
                            $this->logger->warning('Nombre de réponses correctes invalide', ['index' => $index, 'correctAnswers' => $correctAnswers]);
                            unset($questions[$index]);
                            continue;
                        }
                        $uniqueCorrectAnswers = array_unique($correctAnswers);
                        if (count($uniqueCorrectAnswers) !== count($correctAnswers)) {
                            $this->logger->warning('Doublons dans les réponses correctes', ['index' => $index, 'correctAnswers' => $correctAnswers]);
                            unset($questions[$index]);
                            continue;
                        }
                        $optionValues = array_column($question['options'], 'value');
                        foreach ($correctAnswers as $correct) {
                            if (!in_array($correct, $optionValues)) {
                                $this->logger->warning('Réponse correcte invalide', ['index' => $index, 'correctAnswer' => $correct]);
                                unset($questions[$index]);
                                continue 2;
                            }
                        }
                    } elseif ($question['type'] === 'essay' && !isset($question['correctAnswer'])) {
                        $this->logger->warning('Question ouverte sans réponse correcte', ['index' => $index, 'question' => $question]);
                        unset($questions[$index]);
                        continue;
                    }
                }

                $questions = array_values($questions);

                $totalPoints = array_sum(array_column($questions, 'points'));
                if (abs($totalPoints - 20.0) > 0.01) {
                    $this->logger->warning('Total des points incorrect', [
                        'total' => $totalPoints,
                        'attendu' => 20.0,
                        'tentative' => $attempt + 1
                    ]);
                    $questions = $this->adjustPoints($questions);
                }

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

    private function adjustPoints(array $questions): array
    {
        $totalPoints = array_sum(array_column($questions, 'points'));
        $numQuestions = count($questions);
        if ($numQuestions === 0) {
            return $questions;
        }

        $targetTotal = 20.0;
        $scaleFactor = $targetTotal / $totalPoints;

        foreach ($questions as &$question) {
            $question['points'] = round($question['points'] * $scaleFactor, 2);
            if ($question['type'] === 'radio') {
                $question['points'] = max(0.25, min(2.0, $question['points']));
            } elseif ($question['type'] === 'essay') {
                $question['points'] = max(1.0, min(5.0, $question['points']));
            }
        }

        $newTotal = array_sum(array_column($questions, 'points'));
        if (abs($newTotal - $targetTotal) > 0.01) {
            $difference = $targetTotal - $newTotal;
            $perQuestionAdjustment = $difference / $numQuestions;
            foreach ($questions as &$question) {
                $question['points'] += $perQuestionAdjustment;
                $question['points'] = round($question['points'], 2);
                if ($question['type'] === 'radio') {
                    $question['points'] = max(0.25, min(2.0, $question['points']));
                } elseif ($question['type'] === 'essay') {
                    $question['points'] = max(1.0, min(5.0, $question['points']));
                }
            }
        }

        $finalTotal = array_sum(array_column($questions, 'points'));
        if (abs($finalTotal - $targetTotal) > 0.01) {
            $lastIndex = $numQuestions - 1;
            $questions[$lastIndex]['points'] += ($targetTotal - $finalTotal);
            $questions[$lastIndex]['points'] = round($questions[$lastIndex]['points'], 2);
            if ($questions[$lastIndex]['type'] === 'radio') {
                $questions[$lastIndex]['points'] = max(0.25, min(2.0, $questions[$lastIndex]['points']));
            } elseif ($questions[$lastIndex]['type'] === 'essay') {
                $questions[$lastIndex]['points'] = max(1.0, min(5.0, $questions[$lastIndex]['points']));
            }
        }

        return $questions;
    }

    public function analyzePdfErrors(string $pdfContent): array
    {
        $this->logger->info('Analyse des erreurs dans le PDF', [
            'pdf_content_length' => strlen($pdfContent)
        ]);

        $fixedInstructions = "À chaque question, précisez les réponses correctes (une ou plusieurs pour les questions de type radio, une réponse détaillée pour les questions ouvertes). Attribuez des points à chaque question en utilisant des valeurs décimales. Le total des points doit EXACTEMENT égaler 20.";

        $prompt = <<<EOD
Vous êtes un assistant spécialisé dans l'analyse de documents d'examen académique. Votre tâche est d'examiner le contenu d'un PDF pour identifier les erreurs potentielles qui pourraient affecter l'extraction de questions d'examen, en suivant les instructions fournies.

**Contenu du document** :
{$pdfContent}

** instractions** :
{$fixedInstructions}

**Exigences** :
- Identifiez les erreurs telles que :
  - Absence de questions structurées (par exemple, le document ne contient pas de questions claires avec options ou réponses).
  - Questions à choix multiples avec moins de 3 ou plus de 6 options.
  - Questions à choix multiples sans réponses correctes, avec trop de réponses correctes (n ou plus, où n est le nombre d'options), ou avec des doublons dans les réponses correctes.
  - Questions sans points attribués ou total des points différent de 20.
  - Questions ouvertes sans réponse exacte fournie.
  - Texte illisible ou mal structuré (par exemple, texte extrait comme du bruit ou des caractères incohérents).
  - Contenu insuffisant pour un examen (par exemple, trop court ou hors sujet).
  - Incohérences dans le formatage (par exemple, numérotation des questions manquante ou incorrecte).
  - Absence de variabilité dans le nombre d'options pour les questions à choix multiples.
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
        "error": "Certaines questions à choix multiples ont des réponses correctes en doublon.",
        "critical": false
    },
    {
        "error": "Le total des points n'est pas égal à 20.",
        "critical": false
    },
    {
        "error": "Toutes les questions à choix multiples ont le même nombre d'options.",
        "critical": false
    }
]

Analysez le contenu maintenant et retournez la liste des erreurs.
EOD;

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->geminiApiKey, [
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
Vous êtes un correcteur d'examen académique strict mais équitable. Votre tâche est d'évaluer les réponses étudiantes selon ces règles strictes :

**Règles de correction** :
1. Notation :
- Une question sans réponse (vide ou tableau vide pour les questions radio, chaîne vide pour les questions ouvertes) reçoit automatiquement 0 point.
- Pour les questions à choix multiples (radio) :
  - Les réponses correctes sont fournies dans un tableau JSON. La note est proportionnelle au nombre de réponses correctes sélectionnées par rapport au nombre total de réponses correctes.
  - Si toutes les réponses correctes sont sélectionnées et aucune incorrecte, attribuez 100% des points.
  - Si aucune réponse correcte n'est sélectionnée ou si la réponse est vide, attribuez 0.
  - Pour les réponses partielles, calculez la proportion (nombre de bonnes réponses sélectionnées / nombre total de réponses correctes) et attribuez cette proportion des points.
  - Pénalisez de 10% par réponse incorrecte sélectionnée (minimum 0).
- Pour les questions ouvertes (essay) :
  - Une réponse complètement correcte reçoit 100% des points.
  - Une réponse partiellement correcte reçoit entre 30% et 70% des points, selon l'exactitude et la complétude.
  - Une réponse complètement fausse mais pertinente reçoit entre 10% et 20% des points.
  - Une réponse hors sujet, absurde ou vide reçoit 0.

2. Feedback :
- Fournissez un commentaire constructif pour chaque réponse.
- Pour les réponses vides, indiquez "Aucune réponse fournie" comme feedback.
- Pour les réponse fausse, indiquez "Réponse incorrecte" comme feedback
- Indiquez ce qui manque ou est incorrect dans les réponses partielles.
- Proposez des pistes d'amélioration spécifiques.

3. Équité :
- Soyez indulgent avec les réponses approximatives mais montrant un effort.
- Soyez strict avec les réponses manifestement incorrectes ou non sérieuses.
- Respectez strictement les points attribués à chaque question.

**Document de référence** :
{$pdfContent}

**Questions et réponses étudiantes** :
EOD;

        foreach ($questions as $index => $question) {
            $questionId = $question->getId();
            $answer = $answers[$questionId] ?? ($question->getType() === 'radio' ? [] : '');
            $prompt .= "\n**Question " . ($index + 1) . "** (ID: {$questionId}) : " . $question->getTexte();
            $prompt .= "\nPoints : " . $question->getPoints();
            if ($question->getType() === 'radio') {
                $prompt .= "\nOptions : ";
                foreach ($question->getOptions() as $option) {
                    $prompt .= "\n- " . $option->getTexte() . " (" . $option->getValeur() . ")";
                }
                $correctAnswers = json_decode($question->getReponseCorrecte(), true) ?? [];
                $prompt .= "\nRéponses correctes : " . implode(', ', $correctAnswers);
                $prompt .= "\nRéponse de l'étudiant : " . json_encode($answer);
            } elseif ($question->getType() === 'essay') {
                $prompt .= "\nRéponse correcte : " . ($question->getReponseCorrecte() ?? 'Non fournie');
                $prompt .= "\nRéponse de l'étudiant : " . json_encode($answer);
            }
        }

        $prompt .= <<<EOD

**Consignes finales** :
1. Analysez chaque réponse avec rigueur mais bienveillance.
2. Pour les questions à choix multiples, calculez la note en fonction de la proportion de réponses correctes et appliquez une pénalité de 10% par réponse incorrecte.
3. Pour les questions ouvertes, comparez la réponse de l'étudiant à la réponse correcte fournie et attribuez une note basée sur l'exactitude et la complétude.
4. Assurez-vous que les points attribués pour chaque question ne dépassent pas les points maximum indiqués et sont précis à deux décimales.
5. La note finale doit être une somme précise des notes des questions, ne dépassant pas 20.
6. Fournissez un commentaire détaillé pour chaque question, expliquant la note attribuée. Si la réponse est vide, utilisez "Aucune réponse fournie" comme feedback.

**Format de sortie attendu** :
{
    "total_score": [note sur 20, avec deux décimales],
    "questions": {
        "[questionId]": {
            "score": [points obtenus, avec deux décimales],
            "feedback": "Commentaire détaillé expliquant la note..."
        },
        // ...
    }
}

Procédez maintenant à la correction en appliquant strictement ces règles.
EOD;

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->geminiApiKey, [
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

            $totalScore = 0;
            foreach ($questions as $question) {
                $questionId = $question->getId();
                $maxPoints = $question->getPoints();
                $score = $correction['questions'][$questionId]['score'] ?? 0;
                $correction['questions'][$questionId]['score'] = round(min(max(0, (float)$score), $maxPoints), 2);
                $correction['questions'][$questionId]['feedback'] = $correction['questions'][$questionId]['feedback'] ?? 'Aucune réponse fournie';
                $totalScore += $correction['questions'][$questionId]['score'];
            }
            $correction['total_score'] = round(min($totalScore, 20.0), 2);

            $this->logger->info('Correction générée', [
                'total_score' => $correction['total_score'],
                'question_scores' => array_map(function ($q) use ($correction) {
                    return $correction['questions'][$q->getId()]['score'];
                }, $questions)
            ]);
            return $correction;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la correction', ['exception' => $e->getMessage()]);
            throw new \Exception('Erreur lors de la correction de l\'examen : ' . $e->getMessage());
        }
    }
}
