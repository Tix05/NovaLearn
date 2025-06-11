<?php

namespace App\Controller\Api;

use App\Entity\FichierSupport;
use App\Entity\Bibliotheque;
use App\Entity\Agenda;
use App\Repository\FichierSupportRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\EcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Vich\UploaderBundle\Handler\UploadHandler;
use App\Repository\ParcoursRepository;
use Symfony\Component\HttpFoundation\File\File;
use Spatie\PdfToImage\Pdf;

/**
 * @Route("/api")
 */
class BibliothequeController extends AbstractController
{
    private $fichierSupportRepository;
    private $bibliothequeRepository;
    private $entityManager;
    private $security;
    private $logger;
    private $storage;
    private $uploadHandler;
    private $serializer;
    private $parcoursRepository;

    public function __construct(
        FichierSupportRepository $fichierSupportRepository,
        BibliothequeRepository $bibliothequeRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
        StorageInterface $storage,
        UploadHandler $uploadHandler,
        SerializerInterface $serializer,
        ParcoursRepository $parcoursRepository
    ) {
        $this->fichierSupportRepository = $fichierSupportRepository;
        $this->bibliothequeRepository = $bibliothequeRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->uploadHandler = $uploadHandler;
        $this->serializer = $serializer;
        $this->parcoursRepository = $parcoursRepository;
    }

    /**
     * @Route("/bibliotheques", name="api_bibliotheques", methods={"GET"})
     */
    public function getBibliothequeItems(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $baseUrl = $this->getParameter('app.base_url');

        $bibliothequeItems = $this->bibliothequeRepository->findAll();

        $fichierSupports = $this->fichierSupportRepository->findBy([
            'type' => FichierSupport::TYPE_FICHIER,
            'est_publique' => true,
        ]);

        $items = [];

        foreach ($bibliothequeItems as $item) {
            $type = ($item->getType() === 'document') ? 'leçon' : $item->getType();
            $agenda = $item->getAgenda();
            $description = $item->isStatus() && $agenda ? $agenda->getDescription() : $item->getDescription();

            $items[] = [
                '@id' => '/api/bibliotheques/' . $item->getId(),
                'titre' => $item->getTitre(),
                'type' => $type,
                'fichier' => $item->getFichier() ? $baseUrl . '/uploads/bibliotheque/' . $item->getFichier() : null,
                'mentionName' => $item->getMentionName(),
                'niveauNom' => $item->getNiveauNom(),
                'ecName' => $item->getEcName(),
                'parcoursName' => $item->getParcoursName(),
                'status' => $item->isStatus(),
                'description' => $description,
                'isPublished' => $item->isStatus(),
            ];
        }

        // Ajouter les éléments de FichierSupport
        foreach ($fichierSupports as $support) {
            $fileUrl = $support->getFichier()
                ? $baseUrl . '/uploads/supports/' . $support->getFichier()
                : $support->getUrl();

            $items[] = [
                '@id' => '/api/fichier_supports/' . $support->getId(),
                'titre' => $support->getTitre(),
                'type' => $this->mapSupportType($support->getType()),
                'fichier' => $fileUrl,
                'mentionName' => $support->getEc()->getUe()->getMention()->getName(),
                'niveauNom' => $support->getEc()->getUe()->getSemestre()->getNiveau()->getNom(),
                'ecName' => $support->getEc()->getName(),
                'status' => $support->isEstPublique(),
                'description' => null,
                'isPublished' => $support->isEstPublique(),
            ];
        }

        return $this->json([
            'hydra:member' => $items,
            'hydra:totalItems' => count($items),
        ], Response::HTTP_OK, [], ['groups' => ['bibliotheque:list']]);
    }

    /**
     * @Route("/bibliotheques", name="api_create_bibliotheque", methods={"POST"})
     */
    public function createBibliothequeItem(Request $request, EcRepository $ecRepository, ParcoursRepository $parcoursRepository): Response
    {
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $type = $request->request->get('type');
        $ecId = $request->request->get('ec');
        $parcoursName = $request->request->get('parcours');
        $description = $request->request->get('description');
        $status = $request->request->get('status') === '1';

        if (!$file || !$titre || !$type || !$ecId || !$parcoursName || !$description) {
            $this->logger->warning('Données manquantes', [
                'file' => $file ? 'present' : 'missing',
                'titre' => $titre,
                'type' => $type,
                'ec' => $ecId,
                'parcours' => $parcoursName,
                'description' => $description,
            ]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $ecRepository->find($ecId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $ecId]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $parcours = $parcoursRepository->findOneBy(['name' => $parcoursName]);
        if (!$parcours) {
            $this->logger->warning('Parcours non trouvé', ['parcours_name' => $parcoursName]);
            return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si l'utilisateur est professeur (et non admin)
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
        if (!in_array($type, $validTypes)) {
            $this->logger->warning('Type invalide', ['type' => $type]);
            return $this->json(['message' => 'Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice'], Response::HTTP_BAD_REQUEST);
        }

        $bibliotheque = new Bibliotheque();
        $bibliotheque->setTitre($titre);
        $bibliotheque->setType($type);
        $bibliotheque->setEc($ec);
        $bibliotheque->setMention($parcours->getMention());
        $bibliotheque->setParcours($parcours);
        $bibliotheque->setUser($user);
        $bibliotheque->setStatus($status);
        $bibliotheque->setDescription($description);

        // Gérer le fichier manuellement
        try {
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $fileName;
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque', $fileName);
            $bibliotheque->setFichier($fileName);
            $bibliotheque->setFile($file);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'upload du fichier', [
                'error' => $e->getMessage(),
                'titre' => $titre,
            ]);
            return $this->json(['message' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Créer une entrée dans l'agenda si status est true
        if ($status) {
            try {
                $agenda = new Agenda();
                $agenda->setTitre($titre);
                $agenda->setType(Agenda::TYPE_COURS);
                $agenda->setDate(new \DateTimeImmutable());
                $agenda->setDateExpiration((new \DateTimeImmutable())->modify('+2 days'));
                $agenda->setDescription($description);
                $agenda->setNomAuteur($user->getName() ?: $user->getEmail());
                $agenda->setMention($parcours->getMention());
                $agenda->setParcours($parcours);
                $agenda->setNiveau($parcours->getNiveau());
                $agenda->setBibliotheque($bibliotheque);

                // Extraire la première page du PDF si c'est un PDF
                if ($file->getClientOriginalExtension() === 'pdf') {
                    $pdf = new Pdf($filePath);
                    $imageName = uniqid() . '.jpg';
                    $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $imageName;
                    $pdf->setPage(1)->saveImage($imagePath);
                    $agenda->setImage($imageName);
                }

                $this->entityManager->persist($agenda);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la création de l\'entrée agenda', [
                    'error' => $e->getMessage(),
                    'titre' => $titre,
                ]);
                return $this->json(['message' => 'Erreur lors de la création de l\'agenda : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        try {
            $this->entityManager->persist($bibliotheque);
            $this->entityManager->flush();

            $this->logger->info('Élément de bibliothèque créé avec succès', ['id' => $bibliotheque->getId(), 'titre' => $titre]);

            $data = $this->serializer->serialize($bibliotheque, 'json', ['groups' => ['bibliotheque:read']]);

            return new Response(
                $data,
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'élément de bibliothèque', [
                'error' => $e->getMessage(),
                'titre' => $titre,
            ]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/bibliotheques/{id}", name="api_update_bibliotheque", methods={"PATCH"})
     */
    public function updateBibliothequeItem(int $id, Request $request, EcRepository $ecRepository, ParcoursRepository $parcoursRepository): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si l'utilisateur est admin ou professeur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $bibliotheque = $this->bibliothequeRepository->find($id);
        if (!$bibliotheque) {
            $this->logger->warning('Élément non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Élément non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'utilisateur est un professeur associé à l'EC (sauf pour les admins)
        if (!$isAdmin) {
            $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
            if (!$prof || $bibliotheque->getEc()->getProf()->getId() !== $prof->getId()) {
                $this->logger->warning('Non autorisé à modifier cet élément', ['id' => $id, 'prof_id' => $prof ? $prof->getId() : null]);
                return $this->json(['message' => 'Non autorisé à modifier cet élément'], Response::HTTP_FORBIDDEN);
            }
        }

        // Décoder le JSON de la requête
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Erreur de décodage JSON', ['error' => json_last_error_msg()]);
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Log des données reçues
        $this->logger->info('Données JSON reçues', ['data' => $data]);

        // Stocker l'état initial de l'entité
        $initialState = [
            'titre' => $bibliotheque->getTitre(),
            'type' => $bibliotheque->getType(),
            'ec' => $bibliotheque->getEc() ? $bibliotheque->getEc()->getId() : null,
            'parcours' => $bibliotheque->getParcours() ? $bibliotheque->getParcours()->getName() : null,
            'status' => $bibliotheque->isStatus(),
            'fichier' => $bibliotheque->getFichier(),
            'description' => $bibliotheque->getDescription(),
        ];
        $this->logger->info('État initial de l\'entité', $initialState);

        // Mettre à jour les champs uniquement si présents
        if (isset($data['titre'])) {
            $this->logger->info('Mise à jour du titre', ['titre' => $data['titre']]);
            $bibliotheque->setTitre($data['titre']);
        }

        if (isset($data['type'])) {
            $validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
            if (!in_array($data['type'], $validTypes)) {
                $this->logger->warning('Type invalide', ['type' => $data['type']]);
                return $this->json(['message' => 'Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice'], Response::HTTP_BAD_REQUEST);
            }
            $this->logger->info('Mise à jour du type', ['type' => $data['type']]);
            $bibliotheque->setType($data['type']);
        }

        if (isset($data['ec'])) {
            $ec = $ecRepository->find($data['ec']);
            if (!$ec) {
                $this->logger->warning('EC non trouvé', ['ec_id' => $data['ec']]);
                return $this->json(['message' => 'EC non trouvé'], Response::HTTP_BAD_REQUEST);
            }
            // Vérifier si l'utilisateur est un professeur associé à l'EC (sauf pour les admins)
            if (!$isAdmin) {
                $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
                if ($ec->getProf()->getId() !== $prof->getId()) {
                    $this->logger->warning('Non autorisé à modifier avec cet EC', ['ec_id' => $data['ec'], 'prof_id' => $prof->getId()]);
                    return $this->json(['message' => 'Non autorisé à modifier avec cet EC'], Response::HTTP_FORBIDDEN);
                }
            }
            $this->logger->info('Mise à jour de l\'EC', ['ec_id' => $data['ec']]);
            $bibliotheque->setEc($ec);
        }

        if (isset($data['parcours'])) {
            $parcours = $parcoursRepository->findOneBy(['name' => $data['parcours']]);
            if (!$parcours) {
                $this->logger->warning('Parcours non trouvé', ['parcours_name' => $data['parcours']]);
                return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_BAD_REQUEST);
            }
            $this->logger->info('Mise à jour du parcours', ['parcours' => $data['parcours']]);
            $bibliotheque->setParcours($parcours);
            $bibliotheque->setMention($parcours->getMention());
        }

        if (isset($data['description'])) {
            $this->logger->info('Mise à jour de la description', ['description' => $data['description']]);
            $bibliotheque->setDescription($data['description']);
        }

        $agenda = $bibliotheque->getAgenda();
        if (isset($data['status'])) {
            $status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
            $this->logger->info('Mise à jour du status', ['status' => $status]);
            $bibliotheque->setStatus($status);

            if ($status) {
                if (!$agenda) {
                    $agenda = new Agenda();
                    $agenda->setTitre($bibliotheque->getTitre());
                    $agenda->setType(Agenda::TYPE_COURS);
                    $agenda->setDate(new \DateTimeImmutable());
                    $agenda->setDateExpiration((new \DateTimeImmutable())->modify('+2 days'));
                    $agenda->setNomAuteur($user->getName() ?: $user->getEmail());
                    $agenda->setMention($bibliotheque->getMention());
                    $agenda->setParcours($bibliotheque->getParcours());
                    $agenda->setNiveau($bibliotheque->getParcours()->getNiveau());
                    $agenda->setBibliotheque($bibliotheque);

                    // Générer une image si le fichier est un PDF
                    if ($bibliotheque->getFichier() && pathinfo($bibliotheque->getFichier(), PATHINFO_EXTENSION) === 'pdf') {
                        try {
                            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $bibliotheque->getFichier();
                            if (file_exists($filePath)) {
                                $pdf = new Pdf($filePath);
                                $imageName = uniqid() . '.jpg';
                                $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $imageName;
                                $pdf->setPage(1)->saveImage($imagePath);
                                $agenda->setImage($imageName);
                                $this->logger->info('Image générée pour l\'agenda', ['image' => $imageName]);
                            } else {
                                $this->logger->warning('Fichier PDF non trouvé pour générer l\'image', ['file' => $filePath]);
                            }
                        } catch (\Exception $e) {
                            $this->logger->error('Erreur lors de la génération de l\'image pour l\'agenda', ['error' => $e->getMessage()]);
                        }
                    }

                    $this->entityManager->persist($agenda);
                }
                $agenda->setDescription($bibliotheque->getDescription() ?? '');
                $this->entityManager->persist($agenda);
            } else {
                if ($agenda) {
                    try {
                        // Supprimer l'image de l'agenda
                        if ($agenda->getImage()) {
                            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $agenda->getImage();
                            if (file_exists($imagePath)) {
                                if (unlink($imagePath)) {
                                    $this->logger->info('Image de l\'agenda supprimée', ['image' => $agenda->getImage()]);
                                } else {
                                    $this->logger->warning('Échec de la suppression de l\'image de l\'agenda', ['image' => $agenda->getImage()]);
                                }
                            }
                        }
                        // Marquer l'agenda pour suppression
                        $agendaId = $agenda->getId();
                        $this->entityManager->remove($agenda);
                        // Réinitialiser la référence dans Bibliotheque
                        $bibliotheque->setAgenda(null);
                        // Appliquer immédiatement la suppression de l'agenda
                        $this->entityManager->flush();
                        $this->logger->info('Agenda supprimé lors de la dépublication', ['agenda_id' => $agendaId]);

                        // Vérifier que l'agenda a bien été supprimé
                        $agendaCheck = $this->entityManager->getRepository(Agenda::class)->find($agendaId);
                        if ($agendaCheck) {
                            $this->logger->error('L\'agenda n\'a pas été supprimé de la base de données', ['agenda_id' => $agendaId]);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('Erreur lors de la suppression de l\'agenda', ['error' => $e->getMessage(), 'agenda_id' => $agenda->getId()]);
                        return $this->json(['message' => 'Erreur lors de la suppression de l\'agenda : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        } else if ($agenda && isset($data['description']) && $bibliotheque->isStatus()) {
            // Mettre à jour la description de l'agenda si le status reste vrai
            $agenda->setDescription($bibliotheque->getDescription() ?? '');
            $this->entityManager->persist($agenda);
        }

        // Vérifier les changements détectés par Doctrine
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changes = $unitOfWork->getEntityChangeSet($bibliotheque);
        $this->logger->info('Changements détectés par Doctrine', ['data' => $changes]);

        try {
            $this->entityManager->persist($bibliotheque);
            $this->entityManager->flush();

            // Vérifier si un nouvel agenda a été créé après le flush
            $newAgenda = $this->entityManager->getRepository(Agenda::class)->findOneBy(['bibliotheque' => $bibliotheque]);
            if ($newAgenda && !$status) {
                $this->logger->error('Un nouvel agenda a été créé après la dépublication', ['agenda_id' => $newAgenda->getId()]);
            }

            $this->logger->info('État final de l\'entité après flush', [
                'titre' => $bibliotheque->getTitre(),
                'type' => $bibliotheque->getType(),
                'ec' => $bibliotheque->getEc() ? $bibliotheque->getEc()->getId() : null,
                'parcours' => $bibliotheque->getParcours() ? $bibliotheque->getParcours()->getName() : null,
                'status' => $bibliotheque->isStatus(),
                'fichier' => $bibliotheque->getFichier(),
                'description' => $bibliotheque->getDescription(),
            ]);

            $agenda = $bibliotheque->getAgenda();
            $description = $bibliotheque->isStatus() && $agenda ? $agenda->getDescription() : $bibliotheque->getDescription();

            return $this->json([
                'message' => 'Attributs textuels modifiés avec succès',
                '@id' => '/api/bc/' . $bibliotheque->getId(),
                'titre' => $bibliotheque->getTitre(),
                'type' => $bibliotheque->getType(),
                'fichier' => $bibliotheque->getFichier() ? ($this->getParameter('app.base_url') . '/uploads/bibliotheque/' . $bibliotheque->getFichier()) : null,
                'mentionName' => $bibliotheque->getMentionName(),
                'niveauNom' => $bibliotheque->getNiveauNom(),
                'ecName' => $bibliotheque->getEcName(),
                'parcoursName' => $bibliotheque->getParcoursName(),
                'status' => $bibliotheque->isStatus(),
                'description' => $description,
                'isPublished' => $bibliotheque->isStatus(),
            ], Response::HTTP_OK, [], ['groups' => ['bibliotheque:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'élément', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/bibliotheques/{id}/upload", name="api_upload_bibliotheque_file", methods={"POST"})
     */
    public function uploadBibliothequeFile(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si l'utilisateur est admin ou professeur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $bibliotheque = $this->bibliothequeRepository->find($id);
        if (!$bibliotheque) {
            $this->logger->warning('Élément non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Élément non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'utilisateur est un professeur associé à l'EC (sauf pour les admins)
        if (!$isAdmin) {
            $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
            if (!$prof || $bibliotheque->getEc()->getProf()->getId() !== $prof->getId()) {
                $this->logger->warning('Non autorisé à modifier cet élément', ['id' => $id, 'prof_id' => $prof ? $prof->getId() : null]);
                return $this->json(['message' => 'Non autorisé à modifier cet élément'], Response::HTTP_FORBIDDEN);
            }
        }

        $file = $request->files->get('file');
        if (!$file) {
            $this->logger->warning('Aucun fichier fourni');
            return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Validate file type
            $mimeType = $file->getMimeType();
            $validMimeTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            if (!in_array($mimeType, $validMimeTypes)) {
                $this->logger->warning('Type de fichier non supporté', ['mime_type' => $mimeType]);
                return $this->json(['message' => 'Type de fichier non supporté'], Response::HTTP_BAD_REQUEST);
            }

            // Delete the old file manually
            if ($bibliotheque->getFichier()) {
                $oldFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $bibliotheque->getFichier();
                if (file_exists($oldFilePath)) {
                    if (unlink($oldFilePath)) {
                        $this->logger->info('Ancien fichier supprimé manuellement', ['fichier' => $bibliotheque->getFichier()]);
                    } else {
                        $this->logger->warning('Échec de la suppression manuelle de l\'ancien fichier', ['fichier' => $bibliotheque->getFichier()]);
                    }
                } else {
                    $this->logger->warning('Ancien fichier non trouvé sur le disque', ['fichier' => $bibliotheque->getFichier()]);
                }
                $bibliotheque->setFichier(null);
            }

            // Save the new file
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $fileName;
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque', $fileName);
            $bibliotheque->setFichier($fileName);
            $bibliotheque->setFile($file);

            // Update agenda image if status is true and file is a PDF
            $agenda = $bibliotheque->getAgenda();
            if ($bibliotheque->isStatus() && $file->getClientOriginalExtension() === 'pdf') {
                if ($agenda) {
                    if ($agenda->getImage()) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $agenda->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $pdf = new Pdf($filePath);
                    $imageName = uniqid() . '.jpg';
                    $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $imageName;
                    $pdf->setPage(1)->saveImage($imagePath);
                    $agenda->setImage($imageName);
                    $this->entityManager->persist($agenda);
                }
            }

            $this->logger->info('Nouveau fichier enregistré', ['file_name' => $fileName]);

            // Persist changes
            $this->entityManager->persist($bibliotheque);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Fichier modifié avec succès',
                '@id' => '/api/bibliotheques/' . $bibliotheque->getId(),
                'fichier' => $this->getParameter('app.base_url') . '/uploads/bibliotheque/' . $bibliotheque->getFichier(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du fichier', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors du traitement du fichier : ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/bibliotheques/{id}", name="api_delete_bibliotheque", methods={"DELETE"})
     */
    public function deleteBibliothequeItem(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si l'utilisateur est admin ou professeur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && !in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $bibliotheque = $this->bibliothequeRepository->find($id);
        if (!$bibliotheque) {
            $this->logger->warning('Élément non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Élément non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'utilisateur est un professeur associé à l'EC (sauf pour les admins)
        if (!$isAdmin) {
            $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
            if (!$prof || $bibliotheque->getEc()->getProf()->getId() !== $prof->getId()) {
                $this->logger->warning('Non autorisé à supprimer cet élément', ['id' => $id, 'prof_id' => $prof ? $prof->getId() : null]);
                return $this->json(['message' => 'Non autorisé à supprimer cet élément'], Response::HTTP_FORBIDDEN);
            }
        }

        // Supprimer le fichier associé avec VichUploader
        if ($bibliotheque->getFichier()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $bibliotheque->getFichier();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Supprimer l'image de l'agenda si elle existe
        $agenda = $bibliotheque->getAgenda();
        if ($agenda && $agenda->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $agenda->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->entityManager->remove($bibliotheque);
        $this->entityManager->flush();

        $this->logger->info('Élément de bibliothèque supprimé avec succès', ['id' => $id]);

        return $this->json(['message' => 'Élement supprimé avec succès'], Response::HTTP_OK);
    }

    private function mapSupportType($type)
    {
        $types = [
            FichierSupport::TYPE_FICHIER => 'leçon',
            FichierSupport::TYPE_VIDEO => 'video',
            FichierSupport::TYPE_AUDIO => 'audio',
            FichierSupport::TYPE_LIEN => 'link',
        ];

        $type = str_replace("'", "", $type);
        return $types[$type] ?? 'document';
    }
}