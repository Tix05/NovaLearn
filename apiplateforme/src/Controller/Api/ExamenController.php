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
use App\Service\GeminiService;
use App\Service\PdfExtractorService;
use App\Repository\ExamenRepository;
use App\Repository\EtudiantRepository;
use App\Repository\AgendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use TCPDF;

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
    $file = $request->files->get('file');
    $this->logger->info('Données reçues pour créer examen', [
        'data' => $data,
        'file' => $file ? $file->getClientOriginalName() : null
    ]);

    $ecId = $data['ec_id'] ?? null;
    $type = $data['type'] ?? 'pdf';
    $instructions = $data['instructions'] ?? '';
    $titre = $data['titre'] ?? 'Examen';
    $description = $data['description'] ?? ''; // Ajout du champ description
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
    $examen->setDescription($description); // Persistance du champ description
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
            } elseif ($file instanceof UploadedFile && $file->getClientOriginalExtension() === 'pdf') {
                $this->logger->info('Traitement du fichier PDF temporaire', ['filename' => $file->getClientOriginalName()]);
                $tempFileName = 'temp_' . md5(uniqid()) . '.pdf';
                $file->move($this->getParameter('uploads_directory') . '/temp', $tempFileName);
                $pdfContent = $this->pdfExtractorService->extractText(
                    $this->getParameter('uploads_directory') . '/temp/' . $tempFileName
                );
                $this->logger->info('Contenu PDF extrait', ['length' => strlen($pdfContent)]);
            } else {
                $this->logger->error('Aucun fichier ou support valide fourni pour type ia_genere', ['type' => $type]);
                return $this->json(['message' => 'Aucun fichier PDF ou support valide fourni'], 400);
            }

            $instructions = "À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.\n" . $instructions;
            $questions = $this->geminiService->generateExamQuestions($pdfContent, $instructions);
            $this->logger->info('Questions générées', ['question_count' => count($questions)]);
        } elseif ($file instanceof UploadedFile && $file->getClientOriginalExtension() === 'pdf') {
            $this->logger->info('Traitement du fichier PDF temporaire', ['filename' => $file->getClientOriginalName()]);
            $tempFileName = 'temp_' . md5(uniqid()) . '.pdf';
            $file->move($this->getParameter('uploads_directory') . '/temp', $tempFileName);
        } else {
            $this->logger->error('Fichier PDF requis pour type pdf', ['type' => $type]);
            return $this->json(['message' => 'Fichier PDF requis'], 400);
        }

        if ($type === 'ia_genere' && !empty($questions)) {
            $tempFileName = $this->generateExamPdf($examen, $questions);
        }

        return $this->json([
            'message' => 'Prévisualisation de l\'examen générée',
            'temp_file' => $tempFileName ? '/uploads/temp/' . $tempFileName : null,
            'questions' => $questions,
            'titre' => $titre,
            'description' => $description, // Inclure la description dans la réponse
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
    $description = $data['description'] ?? ''; // Ajout du champ description
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
    $examen->setDescription($description); // Persistance du champ description
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

            // Vérification approfondie du fichier temporaire
            clearstatcache();
            $tempFileSize = filesize($tempFilePath);
            if ($tempFileSize === 0) {
                $this->logger->error('Fichier temporaire vide', ['temp_file' => $tempFilePath]);
                return $this->json(['message' => 'Fichier temporaire vide'], 400);
            }

            // Vérifier le type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tempFilePath);
            finfo_close($finfo);
            if ($mime !== 'application/pdf') {
                $this->logger->error('Le fichier temporaire n\'est pas un PDF', ['file' => $tempFilePath, 'mime' => $mime]);
                return $this->json(['message' => 'Le fichier temporaire n\'est pas un PDF'], 400);
            }

            // Vérifier l'extraction de texte pour valider l'intégrité
            try {
                $pdfContent = $this->pdfExtractorService->extractText($tempFilePath);
                $this->logger->info('Contenu extrait du fichier temporaire', [
                    'temp_file' => $tempFilePath,
                    'size' => $tempFileSize,
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

            // Préparer le déplacement
            $fileName = str_replace('temp_', '', basename($tempFilePath));
            $filePath = $examensDir . '/' . $fileName;

            // Copie atomique avec verrouillage
            $source = fopen($tempFilePath, 'rb');
            $dest = fopen($filePath, 'wb');
            if (!$source || !$dest) {
                if ($source) fclose($source);
                if ($dest) fclose($dest);
                $this->logger->error('Échec de l\'ouverture des fichiers pour copie', [
                    'source' => $tempFilePath,
                    'destination' => $filePath
                ]);
                return $this->json(['message' => 'Échec de l\'ouverture des fichiers pour copie'], 500);
            }

            if (flock($source, LOCK_SH) && flock($dest, LOCK_EX)) {
                $bytesCopied = stream_copy_to_stream($source, $dest);
                flock($source, LOCK_UN);
                flock($dest, LOCK_UN);
                fclose($source);
                fclose($dest);

                if ($bytesCopied === false || $bytesCopied === 0) {
                    $this->logger->error('Échec de la copie du fichier', [
                        'source' => $tempFilePath,
                        'destination' => $filePath
                    ]);
                    return $this->json(['message' => 'Échec de la copie du fichier'], 500);
                }
                $this->logger->info('Fichier copié avec succès', [
                    'source' => $tempFilePath,
                    'destination' => $filePath,
                    'bytes' => $bytesCopied
                ]);

                // Supprimer le fichier temporaire après copie réussie
                if (!unlink($tempFilePath)) {
                    $this->logger->warning('Échec de la suppression du fichier temporaire', ['file' => $tempFilePath]);
                }
            } else {
                fclose($source);
                fclose($dest);
                $this->logger->error('Échec du verrouillage des fichiers pour copie', [
                    'source' => $tempFilePath,
                    'destination' => $filePath
                ]);
                return $this->json(['message' => 'Échec du verrouillage des fichiers pour copie'], 500);
            }

            // Vérifications post-déplacement
            clearstatcache();
            $fileSize = filesize($filePath);
            if ($fileSize === 0) {
                $this->logger->error('Fichier vide après copie', ['file' => $filePath]);
                return $this->json(['message' => 'Fichier vide après copie'], 500);
            }

            if (!file_exists($filePath) || !is_readable($filePath)) {
                $this->logger->error('Fichier non trouvé ou non lisible après copie', ['file' => $filePath]);
                return $this->json(['message' => 'Fichier non trouvé ou non lisible après copie'], 500);
            }

            // Vérifier le type MIME du fichier copié
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($mime !== 'application/pdf') {
                $this->logger->error('Le fichier copié n\'est pas un PDF', ['file' => $filePath, 'mime' => $mime]);
                return $this->json(['message' => 'Le fichier copié n\'est pas un PDF'], 500);
            }

            // Vérifier l'extraction de texte du fichier copié
            try {
                $pdfContent = $this->pdfExtractorService->extractText($filePath);
                $this->logger->info('Contenu extrait du fichier copié', [
                    'file' => $filePath,
                    'size' => $fileSize,
                    'content_length' => strlen($pdfContent)
                ]);
                if (empty($pdfContent)) {
                    $this->logger->warning('Contenu du fichier copié vide', ['file' => $filePath]);
                    return $this->json(['message' => 'Contenu du fichier copié vide'], 500);
                }
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'extraction du contenu du fichier copié', [
                    'file' => $filePath,
                    'error' => $e->getMessage()
                ]);
                return $this->json(['message' => 'Fichier copié corrompu: ' . $e->getMessage()], 500);
            }

            // Définir les permissions
            if (!chmod($filePath, 0644)) {
                $this->logger->warning('Échec de la définition des permissions', ['file' => $filePath]);
            } else {
                $this->logger->info('Permissions définies sur le fichier', ['file' => $filePath, 'permissions' => '0644']);
            }
        } else {
            $this->logger->error('Aucun fichier fourni pour soumission');
            return $this->json(['message' => 'Fichier PDF requis'], 400);
        }

        // Mécanisme de secours : si le fichier est corrompu, tenter de régénérer pour ia_genere
        if ($type === 'ia_genere' && !empty($questionsData)) {
            try {
                $newFileName = $this->generateExamPdf($examen, $questionsData);
                $newFilePath = $tempDir . '/' . $newFileName;
                $fileName = str_replace('temp_', '', $newFileName);
                $filePath = $examensDir . '/' . $fileName;

                // Copier le fichier régénéré
                if (!copy($newFilePath, $filePath)) {
                    $this->logger->error('Échec de la copie du fichier régénéré', [
                        'source' => $newFilePath,
                        'destination' => $filePath
                    ]);
                    return $this->json(['message' => 'Échec de la copie du fichier régénéré'], 500);
                }

                // Vérifier le fichier régénéré
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
                unlink($newFilePath); // Nettoyer le fichier temporaire régénéré
            } catch (\Exception $e) {
                $this->logger->warning('Échec de la régénération du PDF', ['error' => $e->getMessage()]);
                // Continuer avec le fichier existant si la régénération échoue
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

            $fixedInstructions = 'À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.';
            $questionsData = $this->geminiService->generateExamQuestions($pdfContent, $fixedInstructions);
            $this->logger->info('Questions générées pour PDF', ['question_count' => count($questionsData)]);
        }

        $examen->setFichier($fileName);

        foreach ($questionsData as $q) {
            $question = new Question();
            $question->setTexte($q['text'] ?? '');
            $question->setType($q['type'] ?? 'essay');
            $question->setPoints($q['points'] ?? 1);
            $question->setExamen($examen);

            if ($q['type'] === 'radio') {
                foreach ($q['options'] as $opt) {
                    $option = new OptionQuestion();
                    $option->setTexte($opt['text'] ?? '');
                    $option->setValeur($opt['value'] ?? '');
                    $option->setQuestion($question);
                    $this->entityManager->persist($option);
                }
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
            'description' => $description, // Inclure la description dans la réponse
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
                'statut' => 'publié', // Indiquer le statut dans la prévisualisation
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

        // Mettre à jour l'examen
        $examen->setAgenda($agenda);
        $examen->setStatut('publié'); // Utiliser "publié" avec l'accent
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
    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ETUDIANT', $user->getRoles())) {
        $this->logger->error('Accès non autorisé pour soumission étudiant', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    $etudiant = $this->etudiantRepository->findOneBy(['user' => $user]);
    if (!$etudiant) {
        $this->logger->warning('Aucune donnée étudiante trouvée', ['user' => $user->getEmail()]);
        return $this->json(['message' => 'Aucune donnée étudiante trouvée'], 404);
    }

    $examen = $this->examenRepository->find($id);
    if (!$examen) {
        $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
        return $this->json(['message' => 'Examen non trouvé'], 404);
    }

    $data = json_decode($request->getContent(), true);
    $answers = $data['answers'] ?? [];

    $pdfContent = $examen->getFichier()
        ? $this->pdfExtractorService->extractText(
            $this->getParameter('uploads_directory') . '/examens/' . $examen->getFichier()
        )
        : '';

    $questions = $examen->getQuestions()->toArray();
    $correction = $this->geminiService->correctExam($answers, $questions, $pdfContent);

    $correctionExamen = new CorrectionExamen();
    $correctionExamen->setNoteTotale($correction['total_score']);
    $correctionExamen->setExamen($examen);
    $correctionExamen->setEtudiant($etudiant);

    $reportFileName = $this->generateCorrectionPdf($examen, $correction, $etudiant);
    $correctionExamen->setFichierRapport($reportFileName);

    foreach ($answers as $questionId => $answer) {
        $reponse = new ReponseEtudiant();
        $reponse->setValeur(json_encode($answer));
        $reponse->setNote($correction['questions'][$questionId]['score'] ?? 0);
        $reponse->setCommentaire($correction['questions'][$questionId]['feedback'] ?? '');
        $reponse->setQuestion($this->entityManager->getReference(Question::class, $questionId));
        $reponse->setEtudiant($etudiant);
        $reponse->setExamen($examen);
        $this->entityManager->persist($reponse);
    }

    // Supprimer l'entrée de l'agenda
    $agenda = $examen->getAgenda();
    if ($agenda) {
        $this->entityManager->remove($agenda);
        $examen->setAgenda(null);
    }

    $this->entityManager->persist($correctionExamen);
    $this->entityManager->persist($examen);
    $this->entityManager->flush();

    $this->logger->info('Examen soumis et corrigé', ['examen_id' => $id, 'etudiant_id' => $etudiant->getId()]);
    return $this->json([
        'message' => 'Examen soumis et corrigé',
        'note' => $correction['total_score'],
        'rapport' => '/uploads/corrections/' . $reportFileName
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
public function deleteCorrection(int $id, CorrectionExamenRepository $correctionExamenRepository): JsonResponse
{
    $this->logger->info('Requête reçue pour /api/correction/{id}', ['id' => $id]);

    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
        $this->logger->error('Accès non autorisé', ['user' => $user ? $user->getEmail() : 'anonyme']);
        return $this->json(['message' => 'Accès non autorisé'], 403);
    }

    $correction = $correctionExamenRepository->find($id);
    if (!$correction) {
        $this->logger->warning('Correction non trouvée', ['correction_id' => $id]);
        return $this->json(['message' => 'Correction non trouvée'], 404);
    }

    try {
        // Supprimer le fichier de correction
        if ($correction->getFichierRapport()) {
            $filePath = $this->getParameter('uploads_directory') . '/corrections/' . $correction->getFichierRapport();
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    $this->logger->warning('Échec de la suppression du fichier de correction', ['file' => $filePath]);
                } else {
                    $this->logger->info('Fichier de correction supprimé', ['file' => $filePath]);
                }
            }
        }

        $this->entityManager->remove($correction);
        $this->entityManager->flush();
        $this->logger->info('Correction supprimée', ['correction_id' => $id]);
        return $this->json(['message' => 'Correction supprimée avec succès'], 200);
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la suppression: ' . $e->getMessage(), ['exception' => $e]);
        return $this->json(['message' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
    }
}

private function generateCorrectionPdf(Examen $examen, array $correction, Etudiant $etudiant): string
{
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Système de Correction');
    $pdf->SetTitle('Rapport de Correction - ' . $examen->getTitre());
    $pdf->SetSubject('Correction Examen');
    $pdf->SetKeywords('Correction, Examen, PDF');
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Rapport de Correction - ' . $examen->getTitre(), 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10);

    $pdf->Cell(0, 10, 'Étudiant: ' . $etudiant->getUser()->getName(), 0, 1);
    $pdf->Cell(0, 10, sprintf('Note Totale: %.2f/20', $correction['total_score']), 0, 1);
    $pdf->Ln(5);

    foreach ($correction['questions'] as $qId => $qData) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, sprintf('Question %s', $qId), 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, 'Feedback: ' . ($qData['feedback'] ?? 'Aucun commentaire'), 0, 'L');
        $pdf->Cell(0, 10, sprintf('Score: %.2f/%d', $qData['score'], $qData['points'] ?? 1), 0, 1);
        $pdf->Ln(5);
    }

    $fileName = md5(uniqid()) . '.pdf';
    $filePath = $this->getParameter('uploads_directory') . '/corrections/' . $fileName;
    $pdf->Output($filePath, 'F');

    // Vérifier l'intégrité du fichier généré
    clearstatcache();
    if (!file_exists($filePath) || !is_readable($filePath)) {
        $this->logger->error('Échec de la création du fichier de correction PDF', ['file' => $filePath]);
        throw new \Exception('Échec de la création du fichier de correction PDF');
    }

    $fileSize = filesize($filePath);
    if ($fileSize === 0) {
        $this->logger->error('Fichier de correction PDF vide après génération', ['file' => $filePath]);
        throw new \Exception('Fichier de correction PDF vide après génération');
    }

    try {
        $pdfContent = $this->pdfExtractorService->extractText($filePath);
        if (empty($pdfContent)) {
            $this->logger->error('Contenu du fichier de correction PDF vide après génération', ['file' => $filePath]);
            throw new \Exception('Contenu du fichier de correction PDF vide après génération');
        }
        $this->logger->info('PDF de correction généré et validé', [
            'file' => $filePath,
            'size' => $fileSize,
            'content_length' => strlen($pdfContent)
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Fichier de correction PDF corrompu après génération', [
            'file' => $filePath,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Fichier de correction PDF corrompu après génération: ' . $e->getMessage());
    }

    return $fileName;
}

    /**
     * @Route("/{id}", name="api_examen_get", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getExamen(string $id): JsonResponse
    {
        $this->logger->info('Requête reçue pour /api/examen/{id}', ['id' => $id]);

        if (!is_numeric($id) || (int)$id <= 0) {
            $this->logger->warning('ID non valide fourni', ['id' => $id]);
            return $this->json(['message' => 'ID invalide, un entier positif est requis'], 400);
        }

        $examen = $this->examenRepository->find((int)$id);
        if (!$examen) {
            $this->logger->warning('Examen non trouvé', ['examen_id' => $id]);
            return $this->json(['message' => 'Examen non trouvé'], 404);
        }

        $questions = array_map(function ($question) {
            $options = $question->getOptions()->map(function ($option) {
                return [
                    'label' => $option->getTexte(),
                    'value' => $option->getValeur()
                ];
            })->toArray();

            return [
                'id' => $question->getId(),
                'text' => $question->getTexte(),
                'type' => $question->getType(),
                'options' => $options,
                'correctAnswer' => $question->getReponseCorrecte(),
                'points' => $question->getPoints()
            ];
        }, $examen->getQuestions()->toArray());

        $this->logger->info('Examen récupéré', ['examen_id' => $id]);
        return $this->json([
            'id' => $examen->getId(),
            'titre' => $examen->getTitre(),
            'description' => $examen->getDescription(),
            'duree' => $examen->getDuree(),
            'fichier' => $examen->getFichier() ? '/uploads/examens/' . $examen->getFichier() : null,
            'questions' => $questions
        ], 200);
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

        // Récupérer tous les examens, pas seulement ceux en "soumis"
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
                'statut' => $statut, // "en_attente" pour soumis, "publié" pour publié
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

            // Supprimer le fichier final
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

            // Supprimer le fichier temporaire associé
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
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln(10);

        foreach ($questions as $index => $q) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, sprintf('Question %d (%d points)', $index + 1, $q['points']), 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->MultiCell(0, 10, $q['text'], 0, 'L');
            $pdf->Ln(5);

            if ($q['type'] === 'radio') {
                foreach ($q['options'] as $opt) {
                    $pdf->Cell(0, 10, '- ' . $opt['text'], 0, 1);
                }
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
        $examens = $this->examenRepository->findBy(['statut' => 'publie']);

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
}