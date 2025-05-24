<?php

namespace App\Controller\Api;

use App\Entity\Examen;
use App\Entity\Question;
use App\Entity\Years;
use App\Entity\OptionQuestion;
use App\Entity\FichierSupport;
use App\Entity\Ec;
use App\Entity\Agenda;
use App\Entity\Notification;
use App\Entity\NotificationGroupe;
use App\Entity\CorrectionExamen;
use App\Entity\ReponseEtudiant;
use App\Entity\EtudiantExamenStatut;
use App\Service\GeminiService;
use App\Service\PdfExtractorService;
use App\Repository\ExamenRepository;
use App\Repository\EtudiantRepository;
use App\Repository\AgendaRepository;
use App\Repository\CorrectionExamenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use TCPDF;
use App\Entity\Etudiant;

class ExamenController extends AbstractController
{
    private $security;
    private $entityManager;
    private $examenRepository;
    private $etudiantRepository;
    private $geminiService;
    private $pdfExtractorService;
    private $logger;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        ExamenRepository $examenRepository,
        EtudiantRepository $etudiantRepository,
        GeminiService $geminiService,
        PdfExtractorService $pdfExtractorService,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->examenRepository = $examenRepository;
        $this->etudiantRepository = $etudiantRepository;
        $this->geminiService = $geminiService;
        $this->pdfExtractorService = $pdfExtractorService;
        $this->logger = $logger;
    }

    /**
     * @Route("/create", name="api_examen_create", methods={"POST"})
     */
    public function createExamen(Request $request): JsonResponse
    {
        $this->logger->info('Requête reçue pour /api/examen/create', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
        ]);

        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        $data = $request->request->all();
        $files = $request->files->get('files'); // Changement de 'file' à 'files'
        $this->logger->info('Données reçues pour créer examen', [
            'data' => $data,
            'files' => $files ? array_map(fn($file) => $file->getClientOriginalName(), (array)$files) : null
        ]);

        $ecId = $data['ec_id'] ?? null;
        $type = $data['type'] ?? 'pdf';
        $instructions = $data['instructions'] ?? '';
        $titre = $data['titre'] ?? 'Examen';
        $description = $data['description'] ?? '';
        $duration = isset($data['duree']) && is_numeric($data['duree']) && $data['duree'] > 0 && $data['duree'] <= 86400 ? (int)$data['duree'] : 3600;

        if (!$ecId) {
            $this->logger->warning('EC ID manquant');
            return $this->json(['message' => 'EC ID requis'], 400);
        }

        $ec = $this->entityManager->getRepository(Ec::class)->find($ecId);
        if (!$ec || $ec->getProf()->getUser()->getId() !== $user->getId()) {
            $this->logger->warning('EC non trouvé ou non autorisé', ['ec_id' => $ecId]);
            return $this->json(['message' => 'EC non trouvé ou non autorisé'], 403);
        }

        $this->logger->info('EC trouvé', ['ec_id' => $ecId]);

        $examen = new Examen();
        $examen->setTitre($titre);
        $examen->setDescription($description);
        $examen->setType($type);
        $examen->setStatut('brouillon');
        $examen->setDuree($duration);
        $examen->setAuteur($user);
        $examen->setEc($ec);

        try {
            $questions = [];
            $pdfContent = '';
            $tempFileName = null;

            if ($type === 'ia_genere') {
                if (strpos($instructions, 'Utiliser les supports:') === 0) {
                    $this->logger->info('Traitement des supports existants', ['instructions' => $instructions]);
                    $supportPart = substr($instructions, strlen('Utiliser les supports: '));
                    $supportPart = preg_replace('/[\r\n].*/s', '', $supportPart);
                    $supportTitres = array_map('trim', explode(',', $supportPart));
                    $supportDetails = [];

                    foreach ($supportTitres as $supportTitre) {
                        if (empty($supportTitre)) {
                            $this->logger->warning('Titre de support vide', ['supportTitre' => $supportTitre]);
                            continue;
                        }

                        $support = $this->entityManager->getRepository(FichierSupport::class)->findOneBy([
                            'titre' => $supportTitre,
                            'ec' => $ec,
                        ]);

                        $this->logger->info('Recherche du support', [
                            'titre' => $supportTitre,
                            'ec_id' => $ecId,
                            'support_trouvé' => $support ? 'oui' : 'non'
                        ]);

                        if (!$support || !$support->getFichier() || !str_ends_with($support->getFichier(), '.pdf')) {
                            $this->logger->error('Support non trouvé ou invalide', ['titre' => $supportTitre, 'ec_id' => $ecId]);
                            return $this->json(['message' => "Support '$supportTitre' non trouvé ou n'est pas un PDF"], 404);
                        }

                        $filePath = $this->getParameter('supports_directory') . '/' . $support->getFichier();
                        $this->logger->info('Vérification du fichier support', [
                            'file_path' => $filePath,
                            'existe' => file_exists($filePath) ? 'oui' : 'non'
                        ]);

                        if (!file_exists($filePath)) {
                            $this->logger->error('Fichier support non trouvé', ['file' => $filePath]);
                            return $this->json(['message' => "Fichier pour support '$supportTitre' introuvable"], 404);
                        }

                        $pdfContent .= $this->pdfExtractorService->extractText($filePath) . "\n";
                        $supportDetails[] = "Support: {$supportTitre} (Type: {$support->getType()})";
                    }

                    if (empty($supportDetails)) {
                        $this->logger->error('Aucun support valide spécifié');
                        return $this->json(['message' => 'Aucun support valide spécifié'], 400);
                    }

                    $instructions = implode("\n", $supportDetails) . "\n" . preg_replace('/^Utiliser les supports:[^\n]*\n?/s', '', $instructions);
                } elseif ($files) {
                    $this->logger->info('Traitement des fichiers PDF uploadés', ['file_count' => count((array)$files)]);
                    $files = (array)$files; // Convertir en tableau si ce n'est pas déjà le cas
                    foreach ($files as $index => $file) {
                        if (!$file instanceof UploadedFile || $file->getClientOriginalExtension() !== 'pdf') {
                            $this->logger->error('Fichier non valide ou non PDF', ['index' => $index, 'filename' => $file ? $file->getClientOriginalName() : 'null']);
                            return $this->json(['message' => "Le fichier à l'index $index n'est pas un PDF valide"], 400);
                        }

                        $tempFileName = 'temp_' . md5(uniqid()) . '_' . $index . '.pdf';
                        $file->move($this->getParameter('uploads_directory') . '/temp', $tempFileName);
                        $filePath = $this->getParameter('uploads_directory') . '/temp/' . $tempFileName;
                        $pdfContent .= $this->pdfExtractorService->extractText($filePath) . "\n";
                        $this->logger->info('Contenu PDF extrait', ['filename' => $tempFileName, 'content_length' => strlen($pdfContent)]);

                        // Supprimer le fichier temporaire après extraction
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    $this->logger->error('Aucun fichier ou support valide fourni pour type ia_genere', ['type' => $type]);
                    return $this->json(['message' => 'Aucun fichier PDF ou support valide fourni'], 400);
                }

                $instructions = "À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.\n" . $instructions;
                $questions = $this->geminiService->generateExamQuestions($pdfContent, $instructions);
                $this->logger->info('Questions générées', ['question_count' => count($questions)]);
            } elseif ($files && count((array)$files) === 1) {
                $file = is_array($files) ? reset($files) : $files;
                if (!$file instanceof UploadedFile || $file->getClientOriginalExtension() !== 'pdf') {
                    $this->logger->error('Fichier PDF requis pour type pdf', ['type' => $type]);
                    return $this->json(['message' => 'Fichier PDF requis'], 400);
                }
                $this->logger->info('Traitement du fichier PDF temporaire', ['filename' => $file->getClientOriginalName()]);
                $tempFileName = 'temp_' . md5(uniqid()) . '.pdf';
                $file->move($this->getParameter('uploads_directory') . '/temp', $tempFileName);
            } else {
                $this->logger->error('Un seul fichier PDF requis pour type pdf', ['type' => $type]);
                return $this->json(['message' => 'Un seul fichier PDF requis pour le type pdf'], 400);
            }

            if ($type === 'ia_genere' && !empty($questions)) {
                $tempFileName = $this->generateExamPdf($examen, $questions);
            }

            return $this->json([
                'message' => 'Prévisualisation de l\'examen générée',
                'temp_file' => $tempFileName ? '/uploads/temp/' . $tempFileName : null,
                'questions' => $questions,
                'titre' => $titre,
                'description' => $description,
                'duree' => $duration
            ], 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'examen: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => 'Erreur serveur lors de la création de l\'examen: ' . $e->getMessage()], 500);
        }
    }

/**
 * @Route("/submit-to-admin", name="api_examen_submit_to_admin", methods={"POST"})
 */
public function submitExamenToAdmin(Request $request): JsonResponse
{
    $this->logger->info('Requête reçue pour /api/examen/submit-to-admin', [
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
    ]);

    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
        $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    $data = $request->request->all();
    $file = $request->files->get('file');
    $tempFile = $data['temp_file'] ?? null;
    $this->logger->info('Données reçues pour soumettre examen', [
        'data' => $data,
        'file' => $file ? $file->getClientOriginalName() : null,
        'temp_file' => $tempFile
    ]);

    $ecId = $data['ec_id'] ?? null;
    $type = $data['type'] ?? 'pdf';
    $instructions = $data['instructions'] ?? '';
    $titre = $data['titre'] ?? 'Examen';
    $description = $data['description'] ?? '';
    $questionsData = json_decode($data['questions'] ?? '[]', true);
    $duration = isset($data['duree']) && is_numeric($data['duree']) && $data['duree'] > 0 && $data['duree'] <= 86400 ? (int)$data['duree'] : 3600;

    if (!$ecId) {
        $this->logger->warning('EC ID manquant');
        return $this->json(['message' => 'EC ID requis'], 400);
    }

    $ec = $this->entityManager->getRepository(Ec::class)->find($ecId);
    if (!$ec || $ec->getProf()->getUser()->getId() !== $user->getId()) {
        $this->logger->warning('EC non trouvé ou non autorisé', ['ec_id' => $ecId]);
        return $this->json(['message' => 'EC non trouvé ou non autorisé'], 403);
    }

    $examen = new Examen();
    $examen->setTitre($titre);
    $examen->setDescription($description);
    $examen->setType($type);
    $examen->setStatut('soumis');
    $examen->setDuree($duration);
    $examen->setAuteur($user);
    $examen->setEc($ec);

    try {
        $fileName = null;
        $filePath = null;
        $uploadsDir = $this->getParameter('uploads_directory');
        $examensDir = $uploadsDir . '/examens';
        $tempDir = $uploadsDir . '/temp';

        // Vérifier les permissions et l'existence des dossiers
        if (!is_dir($examensDir) || !is_writable($examensDir)) {
            $this->logger->error('Dossier examens inaccessible ou non écrivable', ['dir' => $examensDir]);
            return $this->json(['message' => 'Dossier examens inaccessible ou non écrivable'], 500);
        }
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            $this->logger->error('Dossier temporaire inaccessible ou non écrivable', ['dir' => $tempDir]);
            return $this->json(['message' => 'Dossier temporaire inaccessible ou non écrivable'], 500);
        }

        if ($file instanceof UploadedFile && $file->getClientOriginalExtension() === 'pdf') {
            $fileName = md5(uniqid()) . '.pdf';
            $filePath = $examensDir . '/' . $fileName;
            $file->move($examensDir, $fileName);
            $this->logger->info('Fichier téléversé déplacé', [
                'source' => $file->getClientOriginalName(),
                'destination' => $filePath
            ]);
        } elseif ($tempFile) {
            $tempFilePath = $tempDir . str_replace('/uploads/temp', '', $tempFile);
            if (!file_exists($tempFilePath)) {
                $this->logger->error('Fichier temporaire non trouvé', ['temp_file' => $tempFilePath]);
                return $this->json(['message' => 'Fichier temporaire non trouvé'], 404);
            }

            // Vérifications du fichier temporaire
            clearstatcache();
            $fileSize = filesize($tempFilePath);
            if ($fileSize === 0) {
                $this->logger->error('Fichier temporaire vide', ['temp_file' => $tempFilePath]);
                return $this->json(['message' => 'Fichier temporaire vide'], 400);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tempFilePath);
            finfo_close($finfo);
            if ($mime !== 'application/pdf') {
                $this->logger->error('Le fichier temporaire n\'est pas un PDF', ['file' => $tempFilePath, 'mime' => $mime]);
                return $this->json(['message' => 'Le fichier temporaire n\'est pas un PDF'], 400);
            }

            try {
                $pdfContent = $this->pdfExtractorService->extractText($tempFilePath);
                $this->logger->info('Contenu extrait du fichier temporaire', [
                    'temp_file' => $tempFilePath,
                    'size' => $fileSize,
                    'content_length' => strlen($pdfContent)
                ]);
                if (empty($pdfContent)) {
                    $this->logger->warning('Contenu du fichier temporaire vide', ['temp_file' => $tempFilePath]);
                    return $this->json(['message' => 'Contenu du fichier temporaire vide'], 400);
                }
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'extraction du contenu du fichier temporaire', [
                    'file' => $tempFilePath,
                    'error' => $e->getMessage()
                ]);
                return $this->json(['message' => 'Fichier temporaire corrompu: ' . $e->getMessage()], 400);
            }

            $fileName = str_replace('temp_', '', basename($tempFilePath));
            $filePath = $examensDir . '/' . $fileName;

            if (!copy($tempFilePath, $filePath)) {
                $this->logger->error('Échec de la copie du fichier', [
                    'source' => $tempFilePath,
                    'destination' => $filePath
                ]);
                return $this->json(['message' => 'Échec de la copie du fichier'], 500);
            }

            if (!unlink($tempFilePath)) {
                $this->logger->warning('Échec de la suppression du fichier temporaire', ['file' => $tempFilePath]);
            }
        } else {
            $this->logger->error('Aucun fichier fourni pour soumission');
            return $this->json(['message' => 'Fichier PDF requis'], 400);
        }

        // Régénération pour ia_genere si nécessaire
        if ($type === 'ia_genere' && !empty($questionsData)) {
            try {
                $newFileName = $this->generateExamPdf($examen, $questionsData);
                $newFilePath = $tempDir . '/' . $newFileName;
                $fileName = str_replace('temp_', '', $newFileName);
                $filePath = $examensDir . '/' . $fileName;

                if (!copy($newFilePath, $filePath)) {
                    $this->logger->error('Échec de la copie du fichier régénéré', [
                        'source' => $newFilePath,
                        'destination' => $filePath
                    ]);
                    return $this->json(['message' => 'Échec de la copie du fichier régénéré'], 500);
                }

                clearstatcache();
                $fileSize = filesize($filePath);
                if ($fileSize === 0) {
                    $this->logger->error('Fichier régénéré vide', ['file' => $filePath]);
                    return $this->json(['message' => 'Fichier régénéré vide'], 500);
                }

                $pdfContent = $this->pdfExtractorService->extractText($filePath);
                if (empty($pdfContent)) {
                    $this->logger->error('Contenu du fichier régénéré vide', ['file' => $filePath]);
                    return $this->json(['message' => 'Contenu du fichier régénéré vide'], 500);
                }

                $this->logger->info('Fichier régénéré avec succès', ['file' => $filePath, 'size' => $fileSize]);
                unlink($newFilePath);
            } catch (\Exception $e) {
                $this->logger->warning('Échec de la régénération du PDF', ['error' => $e->getMessage()]);
            }
        }

        $erreursAnalyse = [];
        if ($type === 'pdf' && $filePath) {
            $pdfContent = $this->pdfExtractorService->extractText($filePath);
            $this->logger->info('Contenu PDF extrait pour analyse', ['length' => strlen($pdfContent)]);

            $erreursAnalyse = $this->geminiService->analyzePdfErrors($pdfContent);
            $this->logger->info('Analyse des erreurs terminée', ['erreurs' => $erreursAnalyse]);

            foreach ($erreursAnalyse as $erreur) {
                if (isset($erreur['critical']) && $erreur['critical']) {
                    $this->logger->warning('Erreur critique détectée, envoi bloqué', ['erreur' => $erreur['error']]);
                    return $this->json([
                        'message' => $erreur['error'],
                        'erreurs_analyse' => implode('; ', array_column($erreursAnalyse, 'error'))
                    ], 400);
                }
            }

            $fixedInstructions = 'À chaque question, précisez les réponses correctes (une ou plusieurs pour les questions de type radio, une réponse détaillée pour les questions ouvertes). Attribuez des points à chaque question en utilisant des valeurs décimales. Le total des points doit EXACTEMENT égaler 20.';
            $questionsData = $this->geminiService->generateExamQuestions($pdfContent, $fixedInstructions);
            $this->logger->info('Questions générées pour PDF', ['question_count' => count($questionsData)]);
        }

        $examen->setFichier($fileName);

        foreach ($questionsData as $q) {
            $question = new Question();
            $question->setTexte($q['text'] ?? '');
            $question->setType($q['type'] ?? 'essay');
            $question->setPoints((float) ($q['points'] ?? 1.0));
            $question->setExamen($examen);

            if ($q['type'] === 'radio') {
                foreach ($q['options'] as $opt) {
                    $option = new OptionQuestion();
                    $option->setTexte($opt['text'] ?? '');
                    $option->setValeur($opt['value'] ?? '');
                    $option->setQuestion($question);
                    $this->entityManager->persist($option);
                }
                $question->setReponseCorrecte(json_encode($q['correctAnswers'] ?? []));
            } elseif ($q['type'] === 'essay') {
                $question->setReponseCorrecte($q['correctAnswer'] ?? '');
            }

            $this->entityManager->persist($question);
        }

        $this->entityManager->persist($examen);
        $this->entityManager->flush();
        $this->logger->info('Examen soumis à l\'administration', ['examen_id' => $examen->getId()]);

        return $this->json([
            'message' => 'Examen soumis à l\'administration avec succès',
            'id' => $examen->getId(),
            'titre' => $titre,
            'description' => $description,
            'fichier' => $examen->getFichier() ? '/uploads/examens/' . $examen->getFichier() : null,
            'erreurs_analyse' => !empty($erreursAnalyse) ? implode('; ', array_column($erreursAnalyse, 'error')) : null,
            'duree' => $duration
        ], 201);
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la soumission de l\'examen: ' . $e->getMessage(), ['exception' => $e]);
        return $this->json(['message' => 'Erreur serveur lors de la soumission de l\'examen: ' . $e->getMessage()], 500);
    }
}

   /**
     * @Route("/{id}/publish", name="api_examen_publish", methods={"POST"})
     */
    public function publishExamen(
        Request $request,
        int $id,
        ExamenRepository $examenRepository,
        AgendaRepository $agendaRepository
    ): JsonResponse {
        $this->logger->info('Requête reçue pour /api/examen/{id}/publish', ['id' => $id]);

        // Vérifier les autorisations
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        // Vérifier l'existence de l'examen
        $examen = $examenRepository->find($id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen non trouvé'], 404);
        }

        // Vérifier le statut de l'examen
        if (!in_array($examen->getStatut(), ['en_attente', 'soumis'])) {
            $this->logger->warning('L\'examen n\'est pas dans un état publiable', ['examen_id' => $id, 'statut' => $examen->getStatut()]);
            return $this->json(['message' => 'Seuls les examens en attente ou soumis peuvent être publiés'], 400);
        }

        // Décoder les données de la requête
        $data = json_decode($request->getContent(), true);
        if (empty($data['date_debut']) || empty($data['date_fin'])) {
            $this->logger->warning('Dates de publication manquantes', ['examen_id' => $id]);
            return $this->json(['message' => 'Les dates de début et de fin sont requises'], 400);
        }

        try {
            // Convertir les dates
            $dateDebut = new \DateTimeImmutable($data['date_debut']);
            $dateFin = new \DateTimeImmutable($data['date_fin']);

            // Valider les dates
            if ($dateDebut >= $dateFin) {
                $this->logger->warning('Date de fin antérieure ou égale à la date de début', ['examen_id' => $id]);
                return $this->json(['message' => 'La date de fin doit être postérieure à la date de début'], 400);
            }

            // Récupérer l'EC associé à l'examen
            $ec = $examen->getEc();
            if (!$ec) {
                $this->logger->error('Aucun EC associé à l\'examen', ['examen_id' => $id]);
                return $this->json(['message' => 'Aucun élément constitutif associé à l\'examen'], 400);
            }

            // Récupérer mention, niveau et parcours depuis l'EC
            $ue = $ec->getUe();
            $mention = $ue->getMention();
            $niveau = $ue->getSemestre()->getNiveau();

            // Récupérer le parcours depuis la relation ue_parcours
            $parcours = null;
            $ueParcours = $this->entityManager->getRepository('App\Entity\UeParcours')->findOneBy(['ue' => $ue]);
            if ($ueParcours) {
                $parcours = $ueParcours->getParcours();
            }

            if (!$parcours && (!isset($data['preview']) || $data['preview'] !== true)) {
                $this->logger->warning('Aucun parcours associé à l\'UE', ['examen_id' => $id, 'ue_id' => $ue->getId()]);
                return $this->json(['message' => 'Aucun parcours associé à l\'élément constitutif'], 400);
            }

            // Gestion de la prévisualisation
            if (isset($data['preview']) && $data['preview'] === true) {
                $fichierPath = $examen->getFichier();
                $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $fichierPath;
                if ($fichierPath && !file_exists($fullPath)) {
                    $this->logger->warning('Fichier PDF non trouvé', ['examen_id' => $id, 'fichier' => $fichierPath]);
                    $fichierPath = null;
                }

                $previewData = [
                    'titre' => $examen->getTitre(),
                    'description' => $examen->getDescription() ?? 'Examen pour ' . $ec->getName(),
                    'date_debut' => $dateDebut->format('c'),
                    'date_fin' => $dateFin->format('c'),
                    'nom_auteur' => $user->getName(),
                    'mention_id' => $mention ? $mention->getId() : null,
                    'mention_name' => $mention ? $mention->getName() : null,
                    'niveau_id' => $niveau ? $niveau->getId() : null,
                    'niveau_name' => $niveau ? $niveau->getNom() : null,
                    'parcours_id' => $parcours ? $parcours->getId() : null,
                    'parcours_name' => $parcours ? $parcours->getName() : null,
                    'type' => Agenda::TYPE_EXAMEN,
                    'fichier' => $examen->getFichier(),
                    'statut' => 'publié',
                ];

                return $this->json([
                    'message' => 'Prévisualisation réussie',
                    'preview' => true,
                    'data' => $previewData,
                ], 200);
            }

            // Publication de l'examen
            $agenda = new Agenda();
            $agenda->setType(Agenda::TYPE_EXAMEN);
            $agenda->setTitre($examen->getTitre());
            $agenda->setDescription($examen->getDescription() ?? 'Examen pour ' . $ec->getName());
            $agenda->setDate($dateDebut);
            $agenda->setDateExpiration($dateFin);
            $agenda->setCreatedAt(new \DateTimeImmutable());
            $agenda->setUpdatedAt(new \DateTimeImmutable());
            $agenda->setNomAuteur($user->getName());
            $agenda->setMention($mention);
            $agenda->setNiveau($niveau);
            $agenda->setParcours($parcours);
            $agenda->addExamen($examen);

            $examen->setAgenda($agenda);
            $examen->setStatut('publié');
            $examen->setDatePublication($dateDebut);
            $examen->setDateExpiration($dateFin);

            $this->entityManager->persist($agenda);
            $this->entityManager->persist($examen);
            $this->entityManager->flush();

            $this->logger->info('Examen publié avec succès', [
                'examen_id' => $id,
                'agenda_id' => $agenda->getId(),
                'parcours_id' => $parcours ? $parcours->getId() : null,
                'mention_id' => $mention ? $mention->getId() : null,
                'niveau_id' => $niveau ? $niveau->getId() : null,
            ]);

            return $this->json([
                'message' => 'Examen publié avec succès',
                'agenda_id' => $agenda->getId(),
                'examen_id' => $examen->getId(),
                'statut' => $examen->getStatut(),
            ], 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la publication: ' . $e->getMessage(), [
                'exception' => $e,
                'examen_id' => $id,
            ]);
            return $this->json(['message' => 'Erreur lors de la publication: ' . $e->getMessage()], 500);
        }
    }

/**
 * @Route("/{id}/submit", name="api_examen_submit", methods={"POST"})
 */
public function submitExamen(int $id, Request $request): JsonResponse
{
    $this->logger->info('Requête reçue pour soumission d\'examen', [
        'examen_id' => $id,
        'request_content' => $request->getContent()
    ]);

    // 1. Vérification des autorisations
    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
        $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    // 2. Récupération de l'étudiant
    $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
    if (!$etudiant) {
        $this->logger->error('Aucune donnée étudiante trouvée', ['user_id' => $user->getId()]);
        return $this->json(['message' => 'Aucune donnée étudiante trouvée'], 404);
    }

    // 3. Récupération de l'examen
    $examen = $this->examenRepository->find($id);
    if (!$examen) {
        $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
        return $this->json(['message' => 'Examen non trouvé'], 404);
    }

    // 4. Vérification du statut de l'examen
    $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)->findOneBy([
        'etudiant' => $etudiant,
        'examen' => $examen,
    ]);

    if (!$statutExamen) {
        $this->logger->warning('Aucun statut d\'examen trouvé', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId()
        ]);
        return $this->json(['message' => 'Examen non démarré'], 400);
    }

    if ($statutExamen->getStatut() === EtudiantExamenStatut::STATUT_ABANDONNE) {
        $this->logger->warning('Examen abandonné', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId()
        ]);
        return $this->json(['message' => 'Examen abandonné'], 400);
    }

    // 5. Vérification du temps restant
    $tempsRestant = $statutExamen->calculerTempsRestant($examen->getDuree());
    $isAutoSubmit = $tempsRestant <= 0;
    $this->logger->info('Vérification temps restant', [
        'examen_id' => $id,
        'temps_restant' => $tempsRestant,
        'is_auto_submit' => $isAutoSubmit
    ]);

    // 6. Décodage des données de la requête
    $data = json_decode($request->getContent(), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->logger->error('Données JSON invalides', [
            'error' => json_last_error_msg(),
            'content' => $request->getContent()
        ]);
        return $this->json(['message' => 'Données JSON invalides'], 400);
    }

    // 7. Initialiser les réponses
    $answers = $data['answers'] ?? ($statutExamen->getReponses() ?? []);
    $this->logger->info('Réponses reçues', [
        'examen_id' => $id,
        'answer_count' => count($answers),
        'answer_keys' => array_keys($answers)
    ]);

    // 8. Mise à jour des réponses dans le statut
    $statutExamen->setReponses($answers);
    $statutExamen->setTempsRestant(0);
    if ($statutExamen->getStatut() !== EtudiantExamenStatut::STATUT_SOUMIS) {
        $statutExamen->setStatut(EtudiantExamenStatut::STATUT_SOUMIS);
    }
    $this->entityManager->persist($statutExamen);

    // 9. Récupération des questions de l'examen
    $questions = $examen->getQuestions()->toArray();
    $questionIds = array_map(fn($q) => $q->getId(), $questions);

    // 10. Préparation des réponses complètes avec valeurs par défaut
    $completeAnswers = [];
    foreach ($questions as $question) {
        $qId = $question->getId();
        $completeAnswers[$qId] = array_key_exists($qId, $answers) ? $answers[$qId] : ($question->getType() === 'radio' ? [] : '');
    }

    // 11. Extraction du contenu du PDF
    $pdfContent = '';
    if ($examen->getFichier()) {
        $pdfPath = $this->getParameter('uploads_directory') . '/examens/' . $examen->getFichier();
        try {
            $pdfContent = $this->pdfExtractorService->extractText($pdfPath);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'extraction du PDF', [
                'examen_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'Erreur lors de la lecture du fichier d\'examen'], 500);
        }
    }

    // 12. Correction de l'examen
    $this->logger->info('Correction d\'examen avec Gemini', [
        'examen_id' => $id,
        'answer_count' => count($completeAnswers),
        'question_count' => count($questions)
    ]);

    try {
        $correction = $this->geminiService->correctExam($completeAnswers, $questions, $pdfContent);

        // Vérification que la correction contient toutes les questions attendues
        $correctedQuestionIds = array_keys($correction['questions'] ?? []);
        $missingQuestions = array_diff($questionIds, $correctedQuestionIds);
        if (!empty($missingQuestions)) {
            $this->logger->warning('Questions manquantes dans la correction', [
                'examen_id' => $id,
                'missing_question_ids' => $missingQuestions
            ]);
            return $this->json([
                'message' => 'Correction incomplète : certaines questions n\'ont pas été corrigées',
                'missing_question_ids' => $missingQuestions
            ], 500);
        }
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la correction', [
            'examen_id' => $id,
            'error' => $e->getMessage(),
            'answers' => array_keys($completeAnswers),
            'question_ids' => $questionIds
        ]);
        return $this->json(['message' => 'Erreur lors de la correction de l\'examen : ' . $e->getMessage()], 500);
    }

    // 13. Création de l'entité CorrectionExamen
    $correctionExamen = new CorrectionExamen();
    $correctionExamen->setNoteTotale($correction['total_score']);
    $correctionExamen->setExamen($examen);
    $correctionExamen->setEtudiant($etudiant);
    $correctionExamen->setCreatedAt(new \DateTime());
    $correctionExamen->setUpdatedAt(new \DateTime());

    // 14. Génération du PDF de correction
    try {
        $reportFileName = $this->generateCorrectionPdf($examen, $correction, $etudiant);
        $correctionExamen->setFichierRapport($reportFileName);
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la génération du PDF de correction', [
            'examen_id' => $id,
            'error' => $e->getMessage()
        ]);
        return $this->json(['message' => 'Erreur lors de la génération du rapport de correction'], 500);
    }

    // 15. Enregistrement des réponses
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $answer = $completeAnswers[$questionId] ?? ($question->getType() === 'radio' ? [] : '');

        if (empty($answer)) {
            $this->logger->info('Réponse vide pour la question', [
                'examen_id' => $id,
                'question_id' => $questionId,
                'question_type' => $question->getType()
            ]);
        }

        $reponse = $this->entityManager->getRepository(ReponseEtudiant::class)->findOneBy([
            'question' => $question,
            'etudiant' => $etudiant,
            'examen' => $examen
        ]);

        if (!$reponse) {
            $reponse = new ReponseEtudiant();
            $reponse->setQuestion($question);
            $reponse->setEtudiant($etudiant);
            $reponse->setExamen($examen);
        }

        $reponse->setValeur(is_array($answer) ? json_encode($answer) : $answer);
        $reponse->setNote($correction['questions'][$questionId]['score'] ?? 0);
        $reponse->setCommentaire($correction['questions'][$questionId]['feedback'] ?? 'Aucune réponse fournie');
        $reponse->setCorrectionExamen($correctionExamen);

        $this->entityManager->persist($reponse);
        $this->logger->debug('Réponse enregistrée', [
            'question_id' => $questionId,
            'etudiant_id' => $etudiant->getId(),
            'note' => $reponse->getNote()
        ]);
    }

    // 16. Sauvegarde dans la base de données
    try {
        $this->entityManager->persist($correctionExamen);
        $this->entityManager->flush();
        $this->logger->info('Examen soumis et corrigé', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId(),
            'total_score' => $correction['total_score'],
            'auto_submit' => $isAutoSubmit
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de l\'enregistrement dans la base de données', [
            'examen_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return $this->json(['message' => 'Erreur lors de l\'enregistrement des réponses'], 500);
    }

    return $this->json([
        'message' => $isAutoSubmit ? 'Examen soumis automatiquement' : 'Examen soumis et corrigé',
        'note' => $correction['total_score'],
        'rapport' => '/uploads/corrections/' . $reportFileName,
        'auto_submit' => $isAutoSubmit
    ], 200);
}

   /**
     * @Route("/student", name="api_examen_student", methods={"GET"})
     */
    public function getStudentExams(CorrectionExamenRepository $correctionExamenRepository): JsonResponse
    {
        $this->logger->info('Requête reçue pour /api/examen/student');

        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        try {
            $corrections = $correctionExamenRepository->findAll();
            $data = array_map(function ($correction) {
                $examen = $correction->getExamen();
                $etudiant = $correction->getEtudiant();
                return [
                    'id' => $correction->getId(),
                    'examen_id' => $examen->getId(),
                    'titre' => $examen->getTitre(),
                    'etudiant_nom' => $etudiant->getUser()->getName(),
                    'note' => $correction->getNoteTotale(),
                    'rapport' => $correction->getFichierRapport() ? '/uploads/corrections/' . $correction->getFichierRapport() : null,
                    'date_soumission' => $correction->getCreatedAt()->format('Y-m-d H:i'),
                    'mention' => $examen->getEc()->getUe()->getMention() ? $examen->getEc()->getUe()->getMention()->getName() : 'N/A',
                    'niveau' => $examen->getEc()->getUe()->getSemestre() ? $examen->getEc()->getUe()->getSemestre()->getNiveau()->getNom() : 'N/A',
                    'elementConstitutif' => $examen->getEc()->getName(),
                ];
            }, $corrections);

            $this->logger->info('Examens des étudiants récupérés', ['count' => count($data)]);
            return $this->json($data, 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des examens: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/correction/{id}", name="api_correction_delete", methods={"DELETE"})
     */
    public function deleteCorrection(
        int $id,
        CorrectionExamenRepository $correctionExamenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->logger->info('Requête reçue pour suppression de correction', ['correction_id' => $id]);

        // Vérification des permissions
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        // Récupération de la correction
        $correction = $correctionExamenRepository->find($id);
        if (!$correction) {
            $this->logger->warning('Correction non trouvée', ['correction_id' => $id]);
            return $this->json(['message' => 'Correction non trouvée'], 404);
        }

        try {
            // Suppression du fichier physique associé
            if ($correction->getFichierRapport()) {
                $filePath = $this->getParameter('uploads_directory') . '/corrections/' . $correction->getFichierRapport();
                
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        $this->logger->error('Échec de la suppression du fichier', ['file' => $filePath]);
                        throw new \RuntimeException('Échec de la suppression du fichier de correction');
                    }
                    $this->logger->info('Fichier de correction supprimé', ['file' => $filePath]);
                }
            }

            // Suppression des réponses associées - Modified to handle cases where the association might not exist
            try {
                if (method_exists($correction, 'getReponses')) {
                    foreach ($correction->getReponses() as $reponse) {
                        $entityManager->remove($reponse);
                        $this->logger->debug('Réponse supprimée', ['reponse_id' => $reponse->getId()]);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->warning('Erreur lors de la suppression des réponses', [
                    'error' => $e->getMessage(),
                    'correction_id' => $id
                ]);
            }

            // Suppression de la correction
            $entityManager->remove($correction);
            $entityManager->flush();

            $this->logger->info('Correction supprimée avec succès', [
                'correction_id' => $id,
                'fichier' => $correction->getFichierRapport()
            ]);

            return $this->json([
                'message' => 'Correction supprimée avec succès',
                'deleted_id' => $id
            ], 200);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression', [
                'correction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'message' => 'Erreur lors de la suppression de la correction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateCorrectionPdf(Examen $examen, array $correction, Etudiant $etudiant): string
    {
        // Vérifier l'accès au dossier corrections
        $correctionsDir = $this->getParameter('uploads_directory') . '/corrections';
        if (!is_dir($correctionsDir) || !is_writable($correctionsDir)) {
            $this->logger->error('Dossier corrections inaccessible ou non écrivable', ['dir' => $correctionsDir]);
            throw new \Exception('Dossier corrections inaccessible ou non écrivable');
        }

        // Initialiser TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Système de Correction');
        $pdf->SetTitle('Rapport de Correction - ' . $examen->getTitre());
        $pdf->SetSubject('Correction Examen');
        $pdf->SetKeywords('Correction, Examen, PDF');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // En-tête
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Rapport de Correction - ' . $examen->getTitre(), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Étudiant : ' . $etudiant->getUser()->getName(), 0, 1);
        $pdf->Cell(0, 8, 'Élément Constitutif : ' . $examen->getEc()->getName(), 0, 1);
        $pdf->Cell(0, 8, 'Date de soumission : ' . (new \DateTime())->format('d/m/Y H:i'), 0, 1);
        $pdf->Cell(0, 8, sprintf('Note Totale : %.2f/20', $correction['total_score']), 0, 1);
        $pdf->Ln(10);

        // Liste des questions avec numérotation séquentielle
        $questions = $examen->getQuestions()->toArray();
        foreach ($questions as $index => $question) {
            $qId = $question->getId();
            if (!isset($correction['questions'][$qId])) {
                $this->logger->warning('Données de correction manquantes pour la question', ['question_id' => $qId]);
                continue;
            }

            $qData = $correction['questions'][$qId];
            $points = $question->getPoints();

            // Titre de la question
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, sprintf('Question %d (%.2f points)', $index + 1, $points), 0, 1);

            // Texte de la question
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 8, $question->getTexte(), 0, 'L');

            // Feedback
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->MultiCell(0, 8, 'Feedback : ' . ($qData['feedback'] ?? 'Aucun commentaire'), 1, 'L');

            // Score
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, sprintf('Score : %.2f/%.2f', $qData['score'] ?? 0, $points), 0, 1);
            $pdf->Ln(5);
        }

        // Génération du fichier
        $fileName = md5(uniqid()) . '.pdf';
        $filePath = $correctionsDir . '/' . $fileName;

        try {
            $pdf->Output($filePath, 'F');
        } catch (\Exception $e) {
            $this->logger->error('Échec de l\'écriture du fichier PDF', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Échec de l\'écriture du fichier PDF');
        }

        // Vérification de l'intégrité du fichier
        clearstatcache();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->logger->error('Échec de la création du fichier PDF', ['file' => $filePath]);
            throw new \Exception('Échec de la création du fichier PDF');
        }

        $fileSize = filesize($filePath);
        if ($fileSize === 0) {
            $this->logger->error('Fichier PDF vide après génération', ['file' => $filePath]);
            throw new \Exception('Fichier PDF vide après génération');
        }

        try {
            $pdfContent = $this->pdfExtractorService->extractText($filePath);
            if (empty($pdfContent)) {
                $this->logger->error('Contenu du fichier PDF vide après génération', ['file' => $filePath]);
                throw new \Exception('Contenu du fichier PDF vide après génération');
            }
            $this->logger->info('PDF de correction généré et validé', [
                'file' => $filePath,
                'size' => $fileSize,
                'content_length' => strlen($pdfContent)
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Fichier PDF corrompu après génération', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Fichier PDF corrompu après génération');
        }

        return $fileName;
    }

    /**
     * @Route("/teacher", name="api_examen_teacher", methods={"GET"})
     */
    public function getTeacherExams(): JsonResponse
    {
        $this->logger->info('Requête reçue pour /api/examen/teacher');

        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        try {
            $currentYear = $this->entityManager->getRepository(Years::class)
                ->findOneBy(['current' => true]);
            $anneeUniversitaire = $currentYear ? $currentYear->getYear() : 'N/A';

            $examens = $this->examenRepository->findAll();
            $data = array_map(function ($examen) use ($anneeUniversitaire) {
                $statut = $examen->getStatut() === 'soumis' ? 'en_attente' : $examen->getStatut();
                return [
                    'id' => $examen->getId(),
                    'nomPrenom' => $examen->getAuteur()->getName(),
                    'mention' => $examen->getEc()->getUe()->getMention() 
                        ? $examen->getEc()->getUe()->getMention()->getName() 
                        : 'N/A',
                    'niveau' => $examen->getEc()->getUe()->getSemestre() 
                        ? $examen->getEc()->getUe()->getSemestre()->getNiveau()->getNom() 
                        : 'N/A',
                    'elementConstitutif' => $examen->getEc()->getName(),
                    'dateEnvoi' => $examen->getDateCreation()->format('Y-m-d H:i'),
                    'anneeUniversitaire' => $anneeUniversitaire,
                    'statut' => $statut, 
                    'fichier' => $examen->getFichier() ? '/uploads/examens/' . $examen->getFichier() : null,
                ];
            }, $examens);

            $this->logger->info('Examens des enseignants récupérés', ['count' => count($data)]);
            return $this->json($data, 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des examens: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
 * @Route("/{id<\d+>}", name="api_examen_get", methods={"GET"})
 */
public function getExamen(int $id): JsonResponse
{
    $this->logger->info('Requête reçue pour récupérer l\'examen', ['examen_id' => $id]);

    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
        $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
    if (!$etudiant) {
        $this->logger->error('Étudiant non trouvé', ['user_id' => $user->getId()]);
        return $this->json(['message' => 'Étudiant non trouvé'], 404);
    }

    $examen = $this->examenRepository->find($id);
    if (!$examen) {
        $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
        return $this->json(['message' => 'Examen non trouvé'], 404);
    }

    $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)
        ->findOneBy(['etudiant' => $etudiant, 'examen' => $examen]);

    $questions = [];
    foreach ($examen->getQuestions() as $question) {
        $questionData = [
            'id' => $question->getId(),
            'text' => $question->getTexte(),
            'type' => $question->getType(),
            'points' => $question->getPoints(),
        ];

        if ($question->getType() === 'radio') {
            $questionData['options'] = array_map(function ($option) {
                return [
                    'value' => $option->getValeur(),
                    'text' => $option->getTexte()
                ];
            }, $question->getOptions()->toArray());
        }

        $questions[] = $questionData;
    }

    $response = [
        'id' => $examen->getId(),
        'titre' => $examen->getTitre(),
        'description' => $examen->getDescription(),
        'duree' => $examen->getDuree(),
        'statut' => $examen->getStatut(),
        'questions' => $questions,
        'date_publication' => $examen->getDatePublication()?->format('Y-m-d H:i:s'),
        'date_expiration' => $examen->getDateExpiration()?->format('Y-m-d H:i:s')
    ];

    if ($statutExamen) {
        $tempsRestant = $statutExamen->calculerTempsRestant($examen->getDuree());
        if ($tempsRestant <= 0 && $statutExamen->getStatut() === EtudiantExamenStatut::STATUT_EN_COURS) {
            // Sauvegarder les réponses actuelles avant soumission
            $statutExamen->setTempsRestant(0);
            $this->entityManager->persist($statutExamen);
            $this->entityManager->flush();

            // Déclencher la soumission automatique
            $submitResponse = $this->submitExamen($id, new Request(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['answers' => $statutExamen->getReponses() ?? []])
            ));

            $submitData = json_decode($submitResponse->getContent(), true);
            $response['statut'] = EtudiantExamenStatut::STATUT_SOUMIS;
            $response['temps_restant'] = 0;
            $response['reponses'] = $statutExamen->getReponses() ?? [];
            $response['message'] = $submitData['message'] ?? 'Examen soumis automatiquement';
            $response['note'] = $submitData['note'] ?? null;
            $response['rapport'] = $submitData['rapport'] ?? null;
        } else {
            $response['statut'] = $statutExamen->getStatut();
            $response['temps_restant'] = $tempsRestant;
            $response['reponses'] = $statutExamen->getReponses() ?? [];
            $response['debut_examen'] = $statutExamen->getDebutExamen()?->format('Y-m-d H:i:s');
        }
    } else {
        $response['statut'] = 'DISPONIBLE';
        $response['temps_restant'] = $examen->getDuree();
        $response['reponses'] = [];
    }

    $this->logger->debug('Réponse getExamen', [
        'examen_id' => $id,
        'etudiant_id' => $etudiant->getId(),
        'statut' => $response['statut'],
        'temps_restant' => $response['temps_restant']
    ]);

    return $this->json($response);
}

    /**
     * @Route("/{id}", name="api_examen_delete", methods={"DELETE"})
     */
    public function deleteExamen(int $id): JsonResponse
    {
        $this->logger->info('Requête reçue pour /api/examen/{id}', ['id' => $id]);

        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        $examen = $this->examenRepository->find($id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen non trouvé'], 404);
        }

        try {
            // Supprimer les fichiers associés
            $uploadsDir = $this->getParameter('uploads_directory');
            $examensDir = $uploadsDir . '/examens';
            $tempDir = $uploadsDir . '/temp';

            if ($examen->getFichier()) {
                $filePath = $examensDir . '/' . $examen->getFichier();
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        $this->logger->warning('Échec de la suppression du fichier final', ['file' => $filePath]);
                    } else {
                        $this->logger->info('Fichier final supprimé', ['file' => $filePath]);
                    }
                }
            }

            $tempFileName = 'temp_' . $examen->getFichier();
            $tempFilePath = $tempDir . '/' . $tempFileName;
            if (file_exists($tempFilePath)) {
                if (!unlink($tempFilePath)) {
                    $this->logger->warning('Échec de la suppression du fichier temporaire', ['file' => $tempFilePath]);
                } else {
                    $this->logger->info('Fichier temporaire supprimé', ['file' => $tempFilePath]);
                }
            }

            $this->entityManager->remove($examen);
            $this->entityManager->flush();
            $this->logger->info('Examen supprimé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen supprimé avec succès'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    private function generateExamPdf(Examen $examen, array $questions): string
{
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($examen->getAuteur()->getEmail());
    $pdf->SetTitle($examen->getTitre());
    $pdf->SetSubject('Examen Généré');
    $pdf->SetKeywords('Examen, Généré, PDF');
    $pdf->SetHeaderData('', 0, $examen->getTitre(), '' . $examen->getEc()->getName());
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $examen->getTitre(), 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, '20/20 points', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10);

    foreach ($questions as $index => $q) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, sprintf('Question %d (%.2f points)', $index + 1, $q['points']), 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, $q['text'], 0, 'L');
        $pdf->Ln(5);

        if ($q['type'] === 'radio') {
            foreach ($q['options'] as $opt) {
                $isCorrect = in_array($opt['value'], $q['correctAnswers'] ?? []) ? ' (correcte)' : '';
                // Utiliser MultiCell pour permettre le retour à la ligne
                $pdf->MultiCell(0, 10, '- ' . $opt['text'] . $isCorrect, 0, 'L');
            }
        } elseif ($q['type'] === 'essay') {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->MultiCell(0, 10, 'Réponse correcte : ' . ($q['correctAnswer'] ?? 'Non fournie'), 0, 'L');
            $pdf->SetFont('helvetica', '', 12);
        }
        $pdf->Ln(5);
    }

    $fileName = 'temp_' . md5(uniqid()) . '.pdf';
    $filePath = $this->getParameter('uploads_directory') . '/temp/' . $fileName;

    // S'assurer que le dossier temporaire est accessible
    $tempDir = $this->getParameter('uploads_directory') . '/temp';
    if (!is_dir($tempDir) || !is_writable($tempDir)) {
        $this->logger->error('Dossier temporaire inaccessible ou non écrivable', ['dir' => $tempDir]);
        throw new \Exception('Dossier temporaire inaccessible ou non écrivable');
    }

    // Écrire le PDF avec gestion des erreurs
    try {
        $pdf->Output($filePath, 'F');
    } catch (\Exception $e) {
        $this->logger->error('Échec de l\'écriture du fichier PDF', ['file' => $filePath, 'error' => $e->getMessage()]);
        throw new \Exception('Échec de l\'écriture du fichier PDF: ' . $e->getMessage());
    }

    // Vérifier l'existence et la taille du fichier
    clearstatcache();
    if (!file_exists($filePath) || !is_readable($filePath)) {
        $this->logger->error('Échec de la création du fichier PDF', ['file' => $filePath]);
        throw new \Exception('Échec de la création du fichier PDF');
    }

    $fileSize = filesize($filePath);
    if ($fileSize === 0) {
        $this->logger->error('Fichier PDF vide après génération', ['file' => $filePath]);
        throw new \Exception('Fichier PDF vide après génération');
    }

    // Vérifier l'intégrité du PDF avec PdfExtractorService
    try {
        $pdfContent = $this->pdfExtractorService->extractText($filePath);
        if (empty($pdfContent)) {
            $this->logger->error('Contenu du fichier PDF vide après génération', ['file' => $filePath]);
            throw new \Exception('Contenu du fichier PDF vide après génération');
        }
        $this->logger->info('PDF généré et validé', [
            'file' => $filePath,
            'size' => $fileSize,
            'content_length' => strlen($pdfContent)
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Fichier PDF corrompu après génération', [
            'file' => $filePath,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Fichier PDF corrompu après génération: ' . $e->getMessage());
    }

    return $fileName;
}

    /**
     * @Route("/check-expired", name="api_examen_check_expired", methods={"GET"})
     */
    public function checkExpiredExams(): JsonResponse
    {
        $this->logger->info('Vérification des examens expirés');

        try {
            $now = new \DateTime();
            $examens = $this->examenRepository->findBy(['statut' => 'publié']);

            foreach ($examens as $examen) {
                if ($examen->getDateExpiration() < $now) {
                    $agenda = $examen->getAgenda();
                    if ($agenda) {
                        $this->entityManager->remove($agenda);
                        $examen->setAgenda(null);
                        $examen->setStatut('termine');
                        $this->entityManager->persist($examen);
                    }
                }
            }

            $this->entityManager->flush();
            $this->logger->info('Vérification des examens expirés terminée');
            return $this->json(['message' => 'Vérification des examens expirés terminée'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification des examens expirés: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['message' => 'Erreur lors de la vérification: ' . $e->getMessage()], 500);
        }
    }

    /**
 * @Route("/{id}/start", name="api_examen_start", methods={"POST"})
 */
public function startExamen(int $id): JsonResponse
{
    $this->logger->info('Requête reçue pour démarrer l\'examen', ['examen_id' => $id]);

    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
        $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
    if (!$etudiant) {
        $this->logger->error('Étudiant non trouvé', ['user_id' => $user->getId()]);
        return $this->json(['message' => 'Étudiant non trouvé'], 404);
    }

    $examen = $this->examenRepository->find($id);
    if (!$examen) {
        $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
        return $this->json(['message' => 'Examen non trouvé'], 404);
    }

    $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)
        ->findOneBy(['etudiant' => $etudiant, 'examen' => $examen]);

    if ($statutExamen) {
        if ($statutExamen->getStatut() === EtudiantExamenStatut::STATUT_EN_COURS) {
            $tempsRestant = $statutExamen->calculerTempsRestant($examen->getDuree());
            if ($tempsRestant <= 0) {
                // Sauvegarder les réponses actuelles avant soumission
                $statutExamen->setTempsRestant(0);
                $this->entityManager->persist($statutExamen);
                $this->entityManager->flush();

                // Déclencher la soumission automatique
                $submitResponse = $this->submitExamen($id, new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    json_encode(['answers' => $statutExamen->getReponses() ?? []])
                ));

                $submitData = json_decode($submitResponse->getContent(), true);
                return $this->json([
                    'message' => $submitData['message'] ?? 'Temps écoulé, examen soumis automatiquement',
                    'temps_restant' => 0,
                    'reponses' => $statutExamen->getReponses() ?? [],
                    'note' => $submitData['note'] ?? null,
                    'rapport' => $submitData['rapport'] ?? null
                ], 200);
            }

            $this->entityManager->flush();
            $this->logger->info('Examen déjà en cours', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId(),
                'temps_restant' => $tempsRestant
            ]);

            return $this->json([
                'message' => 'Examen déjà en cours',
                'temps_restant' => $tempsRestant,
                'reponses' => $statutExamen->getReponses() ?? []
            ], 200);
        }

        $this->logger->warning('Examen déjà soumis ou abandonné', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId(),
            'statut' => $statutExamen->getStatut()
        ]);
        return $this->json(['message' => 'Examen déjà soumis ou abandonné'], 400);
    }

    $statutExamen = new EtudiantExamenStatut();
    $statutExamen->setEtudiant($etudiant);
    $statutExamen->setExamen($examen);
    $statutExamen->setStatut(EtudiantExamenStatut::STATUT_EN_COURS);
    $statutExamen->setDebutExamen(new \DateTime());
    $statutExamen->initialiserTempsRestant($examen->getDuree());

    $this->entityManager->persist($statutExamen);
    $this->entityManager->flush();

    $this->logger->info('Examen démarré', [
        'examen_id' => $id,
        'etudiant_id' => $etudiant->getId(),
        'temps_restant' => $statutExamen->getTempsRestant()
    ]);

    return $this->json([
        'message' => 'Examen démarré',
        'temps_restant' => $statutExamen->getTempsRestant(),
        'reponses' => []
    ], 200);
}

    /**
     * @Route("/{id}/abandon", name="api_examen_abandon", methods={"POST"})
     */
    public function abandonExamen(int $id): JsonResponse
    {
        $this->logger->info('Requête reçue pour abandonner l\'examen', ['examen_id' => $id]);

        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
        if (!$etudiant) {
            $this->logger->error('Aucune donnée étudiante trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], 404);
        }

        $examen = $this->examenRepository->find($id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen non trouvé'], 404);
        }

        $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)->findOneBy([
            'etudiant' => $etudiant,
            'examen' => $examen,
        ]);

        if (!$statutExamen || $statutExamen->getStatut() !== EtudiantExamenStatut::STATUT_EN_COURS) {
            $this->logger->warning('Examen non démarré ou déjà soumis/abandonné', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId()
            ]);
            return $this->json(['message' => 'Examen non démarré ou déjà soumis/abandonné'], 400);
        }

        $statutExamen->setStatut(EtudiantExamenStatut::STATUT_ABANDONNE);
        $statutExamen->setTempsRestant(0);
        $this->entityManager->persist($statutExamen);
        $this->entityManager->flush();

        $this->logger->info('Examen abandonné', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId()
        ]);

        return $this->json(['message' => 'Examen abandonné'], 200);
    }

/**
 * @Route("/{id}/save-progress", name="api_examen_save_progress", methods={"POST"})
 */
public function saveExamProgress(int $id, Request $request): JsonResponse
{
    try {
        // 1. Authentification
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            return $this->json(['message' => 'Accès non autorisé'], 403);
        }

        // 2. Récupération de l'étudiant
        $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
        if (!$etudiant) {
            $this->logger->error('Étudiant non trouvé', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Étudiant non trouvé'], 404);
        }

        // 3. Récupération de l'examen
        $examen = $this->examenRepository->find($id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen non trouvé'], 404);
        }

        // 4. Vérification du statut
        $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)
            ->findOneBy(['etudiant' => $etudiant, 'examen' => $examen]);

        if (!$statutExamen || $statutExamen->getStatut() !== EtudiantExamenStatut::STATUT_EN_COURS) {
            $this->logger->warning('Examen non démarré ou terminé', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId()
            ]);
            return $this->json(['message' => 'Examen non démarré ou terminé'], 400);
        }

        // 5. Traitement des données
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Données JSON invalides', ['error' => json_last_error_msg()]);
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }

        $answers = $data['answers'] ?? [];
        $tempsRestant = isset($data['temps_restant']) ? (int)$data['temps_restant'] : null;

        // 6. Calcul du temps restant serveur pour éviter les manipulations côté client
        $tempsRestantServeur = $statutExamen->calculerTempsRestant($examen->getDuree());
        $tempsRestant = $tempsRestant !== null ? max(0, min($tempsRestantServeur, $tempsRestant)) : $tempsRestantServeur;

        // 7. Si le temps est écoulé, soumettre automatiquement
        if ($tempsRestant <= 0) {
            $this->logger->info('Temps écoulé, soumission automatique déclenchée', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId()
            ]);
            // Sauvegarder les réponses immédiatement
            $statutExamen->setReponses($answers);
            $statutExamen->setTempsRestant(0);
            $this->entityManager->persist($statutExamen);
            $this->entityManager->flush();

            // Soumettre les réponses actuelles
            $submitResponse = $this->submitExamen($id, new Request(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['answers' => $answers])
            ));

            return $this->json([
                'message' => 'Temps écoulé, examen soumis automatiquement',
                'submit_response' => json_decode($submitResponse->getContent(), true)
            ], 200);
        }

        // 8. Mise à jour de l'entité
        $statutExamen->setReponses($answers);
        $statutExamen->setTempsRestant($tempsRestant);

        // 9. Sauvegarde
        $this->entityManager->persist($statutExamen);
        $this->entityManager->flush();

        // 10. Réponse
        $this->logger->info('Progression sauvegardée', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId(),
            'temps_restant' => $tempsRestant,
            'answer_count' => count($answers)
        ]);

        return $this->json([
            'message' => 'Progression sauvegardée',
            'temps_restant' => $tempsRestant,
            'derniere_activite' => $statutExamen->getDerniereActivite()->format('Y-m-d H:i:s')
        ]);

    } catch (\Exception $e) {
        $this->logger->error('Erreur saveExamProgress: ' . $e->getMessage(), [
            'exception' => $e,
            'examen_id' => $id,
            'user_id' => $user ? $user->getId() : null
        ]);
        
        return $this->json([
            'message' => 'Erreur lors de la sauvegarde',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
 * @Route("/{id}/get-time", name="api_examen_get_time", methods={"GET"})
 */
public function getExamTime(int $id): JsonResponse
{
    try {
        // Vérification authentification
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
            $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
            throw new \Exception('Accès non autorisé');
        }

        $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
        if (!$etudiant) {
            $this->logger->error('Étudiant non trouvé', ['user_id' => $user->getId()]);
            throw new \Exception('Étudiant non trouvé');
        }

        $examen = $this->examenRepository->find($id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            throw new \Exception('Examen non trouvé');
        }

        $statutExamen = $this->entityManager->getRepository(EtudiantExamenStatut::class)
            ->findOneBy([
                'etudiant' => $etudiant,
                'examen' => $examen
            ]);

        if (!$statutExamen) {
            $this->logger->warning('Aucun statut d\'examen trouvé', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId()
            ]);
            throw new \Exception('Aucun statut d\'examen trouvé');
        }

        // Calculer le temps restant
        $tempsRestant = $statutExamen->calculerTempsRestant($examen->getDuree());
        
        // Si le temps est écoulé, soumettre automatiquement
        if ($tempsRestant <= 0 && $statutExamen->getStatut() === EtudiantExamenStatut::STATUT_EN_COURS) {
            $this->logger->info('Temps écoulé, soumission automatique déclenchée', [
                'examen_id' => $id,
                'etudiant_id' => $etudiant->getId()
            ]);
            // Sauvegarder l'état actuel
            $statutExamen->setTempsRestant(0);
            $this->entityManager->persist($statutExamen);
            $this->entityManager->flush();

            // Soumettre les réponses actuelles
            $submitResponse = $this->submitExamen($id, new Request(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['answers' => $statutExamen->getReponses() ?? []])
            ));

            $submitData = json_decode($submitResponse->getContent(), true);
            return $this->json([
                'message' => $submitData['message'] ?? 'Temps écoulé, examen soumis automatiquement',
                'temps_restant' => 0,
                'statut' => EtudiantExamenStatut::STATUT_SOUMIS,
                'derniere_activite' => $statutExamen->getDerniereActivite()?->format('Y-m-d H:i:s'),
                'note' => $submitData['note'] ?? null,
                'rapport' => $submitData['rapport'] ?? null
            ]);
        }

        $this->entityManager->persist($statutExamen);
        $this->entityManager->flush();

        // Journalisation pour débogage
        $this->logger->debug('Statut examen', [
            'examen_id' => $id,
            'etudiant_id' => $etudiant->getId(),
            'statut' => $statutExamen->getStatut(),
            'temps_restant' => $tempsRestant,
            'debut_examen' => $statutExamen->getDebutExamen()?->format('Y-m-d H:i:s'),
            'derniere_activite' => $statutExamen->getDerniereActivite()?->format('Y-m-d H:i:s')
        ]);

        return $this->json([
            'temps_restant' => $tempsRestant,
            'derniere_activite' => $statutExamen->getDerniereActivite()?->format('Y-m-d H:i:s'),
            'statut' => $statutExamen->getStatut()
        ]);

    } catch (\Exception $e) {
        $this->logger->error('Erreur getExamTime', [
            'error' => $e->getMessage(),
            'examen_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return $this->json(['message' => $e->getMessage()], 500);
    }
}

    private function calculerTempsRestant(EtudiantExamenStatut $statut): int
    {
        if ($statut->getStatut() !== EtudiantExamenStatut::STATUT_EN_COURS) {
            return 0;
        }

        $dureeExamen = $statut->getExamen()->getDuree() ?? 3600;
        
        // Si pas encore commencé
        if (!$statut->getDebutExamen()) {
            return $dureeExamen;
        }

        // Calcul basé sur le temps écoulé depuis le début
        $now = new \DateTime();
        $tempsEcoule = $now->getTimestamp() - $statut->getDebutExamen()->getTimestamp();
        $tempsRestant = $dureeExamen - $tempsEcoule;

        // Protection contre les valeurs négatives
        return max(0, $tempsRestant);
    }
}