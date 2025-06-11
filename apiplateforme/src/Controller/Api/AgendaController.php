<?php

namespace App\Controller\Api;

use App\Entity\Agenda;
use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Entity\Parcours;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Service\AgendaService;
use App\Repository\AgendaRepository;
use App\Repository\ParcoursRepository;
use App\Repository\MentionRepository;
use App\Repository\NiveauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Route("/api/agenda")
 */
class AgendaController extends AbstractController
{
    private $security;
    private $agendaService;
    private $entityManager;
    private $agendaRepository;
    private $logger;
    private $storage;
    private $uploadHandler;
    private $serializer;
    private $parcoursRepository;
    private $mentionRepository;
    private $niveauRepository;

    public function __construct(
        Security $security,
        AgendaService $agendaService,
        EntityManagerInterface $entityManager,
        AgendaRepository $agendaRepository,
        LoggerInterface $logger,
        StorageInterface $storage,
        UploadHandler $uploadHandler,
        SerializerInterface $serializer,
        ParcoursRepository $parcoursRepository,
        MentionRepository $mentionRepository,
        NiveauRepository $niveauRepository
    ) {
        $this->security = $security;
        $this->agendaService = $agendaService;
        $this->entityManager = $entityManager;
        $this->agendaRepository = $agendaRepository;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->uploadHandler = $uploadHandler;
        $this->serializer = $serializer;
        $this->parcoursRepository = $parcoursRepository;
        $this->mentionRepository = $mentionRepository;
        $this->niveauRepository = $niveauRepository;
    }

    /**
     * @Route("/student", name="api_agenda_student", methods={"GET"})
     */
    public function getStudentAgenda(): JsonResponse
    {
        $user = $this->security->getUser();
        $etudiant = $user->getEtudiants()->first();

        if (!$etudiant) {
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], 404);
        }

        $agendaData = $this->agendaService->getStudentAgendaData($etudiant);

        return $this->json($agendaData);
    }

    /**
     * @Route("/teacher", name="api_agenda_teacher", methods={"GET"})
     */
    public function getTeacherAgenda(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $prof = $this->entityManager->getRepository(Prof::class)
            ->findOneBy(['user' => $user]);

        if (!$prof) {
            return $this->json(['error' => 'Profil enseignant non trouvé'], 404);
        }

        $ecs = $prof->getEcs();
        $mentions = [];
        foreach ($ecs as $ec) {
            $ue = $ec->getUe();
            $mention = $ue->getMention();
            if ($mention && !in_array($mention, $mentions, true)) {
                $mentions[] = $mention;
            }
        }

        if (empty($mentions)) {
            return $this->json(['error' => 'Aucune mention associée à cet enseignant'], 404);
        }

        $mentionIds = array_map(function($mention) { return $mention->getId(); }, $mentions);

        $cours = $this->agendaService->getTeacherAgendaData($mentionIds, 'COURS');
        $evenements = $this->agendaService->getTeacherAgendaData($mentionIds, 'EVENEMENT');

        return $this->json([
            'cours' => $cours,
            'evenements' => $evenements
        ]);
    }

    /**
 * @Route("/admin", name="api_agendas_admin", methods={"GET"})
 */
public function getAgendaItems(): Response
{
    $user = $this->security->getUser();
    if (!$user) {
        $this->logger->error('Aucun utilisateur authentifié');
        return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
    }

    if (!in_array('ROLE_ADMIN', $user->getRoles())) {
        $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
        return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
    }

    $agendaItems = $this->agendaRepository->findAll();
    $items = [];

    foreach ($agendaItems as $item) {
        $items[] = [
            '@id' => '/api/agenda/admin/' . $item->getId(),
            'titre' => $item->getTitre(),
            'description' => $item->getDescription(),
            'date' => $item->getDate()->format('c'),
            'dateExpiration' => $item->getDateExpiration()->format('c'),
            'type' => $item->getType(),
            'image' => $item->getImage() ? '/uploads/agenda/images/' . $item->getImage() : null,
            'video' => $item->getVideo() ? '/uploads/agenda/videos/' . $item->getVideo() : null,
            'url' => $item->getUrl(),
            'nomAuteur' => $item->getNomAuteur(),
            'mentionName' => $item->getMention() ? $item->getMention()->getName() : null,
            'parcoursName' => $item->getParcours() ? $item->getParcours()->getName() : null,
            'niveauNom' => $item->getNiveau() ? $item->getNiveau()->getNom() : null,
            'mentionId' => $item->getMention() ? $item->getMention()->getId() : null,
            'niveauId' => $item->getNiveau() ? $item->getNiveau()->getId() : null,
            'parcoursId' => $item->getParcours() ? $item->getParcours()->getId() : null,
        ];
    }

    return $this->json([
        'hydra:member' => $items,
        'hydra:totalItems' => count($items),
    ], Response::HTTP_OK, [], ['groups' => ['agenda:read']]);
}

    /**
     * @Route("/admin", name="api_create_agenda", methods={"POST"})
     */
    public function createAgendaItem(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $titre = $request->request->get('titre');
        $description = $request->request->get('description');
        $date = $request->request->get('date');
        $dateExpiration = $request->request->get('dateExpiration');
        $type = $request->request->get('type');
        $mentionId = $request->request->get('mention');
        $parcoursId = $request->request->get('parcours');
        $niveauId = $request->request->get('niveau');
        $url = $request->request->get('url');
        $imageFile = $request->files->get('image');
        $videoFile = $request->files->get('video');

        if (!$titre || !$description || !$date || !$dateExpiration || !$type) {
            $this->logger->warning('Données manquantes', [
                'titre' => $titre,
                'description' => $description,
                'date' => $date,
                'dateExpiration' => $dateExpiration,
                'type' => $type,
            ]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        if ($type === Agenda::TYPE_EXAMEN) {
            $this->logger->warning('Création d\'examen non autorisée');
            return $this->json(['message' => 'Création d\'examen non autorisée'], Response::HTTP_FORBIDDEN);
        }

        $validTypes = [Agenda::TYPE_COURS, Agenda::TYPE_EVENEMENT];
        if (!in_array($type, $validTypes)) {
            $this->logger->warning('Type invalide', ['type' => $type]);
            return $this->json(['message' => 'Type invalide. Les types autorisés sont : COURS, EVENEMENT'], Response::HTTP_BAD_REQUEST);
        }

        $mention = $mentionId ? $this->mentionRepository->find($mentionId) : null;
        $parcours = $parcoursId ? $this->parcoursRepository->find($parcoursId) : null;
        $niveau = $niveauId ? $this->niveauRepository->find($niveauId) : null;

        if ($type === Agenda::TYPE_COURS && (!$mention || !$niveau)) {
            $this->logger->warning('Mention et niveau requis pour COURS');
            return $this->json(['message' => 'Mention et niveau requis pour les cours'], Response::HTTP_BAD_REQUEST);
        }

        $agenda = new Agenda();
        $agenda->setTitre($titre);
        $agenda->setDescription($description);
        $agenda->setType($type);
        $agenda->setDate(new \DateTimeImmutable($date));
        $agenda->setDateExpiration(new \DateTimeImmutable($dateExpiration));
        $agenda->setNomAuteur($user->getName() ?: $user->getEmail());
        $agenda->setMention($mention);
        $agenda->setParcours($parcours);
        $agenda->setNiveau($niveau);
        $agenda->setUrl($url);

        try {
            if ($imageFile) {
                $mimeType = $imageFile->getMimeType();
                $validImageMimeTypes = ['image/jpeg', 'image/png'];
                if (!in_array($mimeType, $validImageMimeTypes)) {
                    $this->logger->warning('Type de fichier image non supporté', ['mime_type' => $mimeType]);
                    return $this->json(['message' => 'Type de fichier image non supporté'], Response::HTTP_BAD_REQUEST);
                }
                $imageName = uniqid() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->move($this->getParameter('kernel.project_dir'). '/public/uploads/agenda/images', $imageName);
                $agenda->setImage($imageName);
                $agenda->setImageFile($imageFile);
            }

            if ($videoFile) {
                $mimeType = $videoFile->getMimeType();
                $validVideoMimeTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                if (!in_array($mimeType, $validVideoMimeTypes)) {
                    $this->logger->warning('Type de fichier vidéo non supporté', ['mime_type' => $mimeType]);
                    return $this->json(['message' => 'Type de fichier vidéo non supporté'], Response::HTTP_BAD_REQUEST);
                }
                $videoName = uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $videoFile->move($this->getParameter('kernel.project_dir' . '/public/uploads/agenda/videos', $videoName));
                $agenda->setVideo($videoName);
                $agenda->setVideoFile($videoFile);
            }

            $this->entityManager->persist($agenda);
            $this->entityManager->flush();

            $this->logger->info('Agenda créé avec succès', ['id' => $agenda->getId(), 'titre' => $titre]);

            return $this->json([
                '@id' => '/api/agenda/admin/' . $agenda->getId(),
                'titre' => $agenda->getTitre(),
                'description' => $agenda->getDescription(),
                'date' => $agenda->getDate()->format('c'),
                'dateExpiration' => $agenda->getDateExpiration()->format('c'),
                'type' => $agenda->getType(),
                'image' => $agenda->getImage() ? '/uploads/agenda/images/' . $agenda->getImage() : null,
                'video' => $agenda->getVideo() ? '/uploads/agenda/videos/' . $agenda->getVideo() : null,
                'url' => $agenda->getUrl(),
                'nomAuteur' => $agenda->getNomAuteur(),
                'mentionName' => $agenda->getMention() ? $agenda->getMention()->getName() : null,
                'parcoursName' => $agenda->getParcours() ? $agenda->getParcours()->getName() : null,
                'niveauNom' => $agenda->getNiveau() ? $agenda->getNiveau()->getNom() : null,
                'mentionId' => $agenda->getMention() ? $agenda->getMention()->getId() : null,
                'niveauId' => $agenda->getNiveau() ? $agenda->getNiveau()->getId() : null,
                'parcoursId' => $agenda->getParcours() ? $agenda->getParcours()->getId() : null,
            ], Response::HTTP_CREATED, [], ['groups' => ['agenda:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'agenda', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/admin/{id}", name="api_update_agenda", methods={"PATCH"})
     */
    public function updateAgendaItem(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $agenda = $this->agendaRepository->find($id);
        if (!$agenda) {
            $this->logger->warning('Agenda non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Agenda non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Erreur de décodage JSON', ['error' => json_last_error_msg()]);
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        $initialState = [
            'titre' => $agenda->getTitre(),
            'description' => $agenda->getDescription(),
            'date' => $agenda->getDate()->format('c'),
            'dateExpiration' => $agenda->getDateExpiration()->format('c'),
            'type' => $agenda->getType(),
            'image' => $agenda->getImage(),
            'video' => $agenda->getVideo(),
            'url' => $agenda->getUrl(),
            'mention' => $agenda->getMention() ? $agenda->getMention()->getId() : null,
            'parcours' => $agenda->getParcours() ? $agenda->getParcours()->getId() : null,
            'niveau' => $agenda->getNiveau() ? $agenda->getNiveau()->getId() : null,
        ];
        $this->logger->info('État initial de l\'agenda', $initialState);

        if ($agenda->getType() === Agenda::TYPE_EXAMEN) {
            if (isset($data['titre'])) {
                $agenda->setTitre($data['titre']);
            }
            if (isset($data['description'])) {
                $agenda->setDescription($data['description']);
            }
        } else {
            if (isset($data['titre'])) {
                $agenda->setTitre($data['titre']);
            }
            if (isset($data['description'])) {
                $agenda->setDescription($data['description']);
            }
            if (isset($data['date'])) {
                $agenda->setDate(new \DateTimeImmutable($data['date']));
            }
            if (isset($data['dateExpiration'])) {
                $agenda->setDateExpiration(new \DateTimeImmutable($data['dateExpiration']));
            }
            if (isset($data['url'])) {
                $agenda->setUrl($data['url']);
            }
            if (isset($data['mention'])) {
                $mention = $this->mentionRepository->find($data['mention']);
                if (!$mention) {
                    return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_BAD_REQUEST);
                }
                $agenda->setMention($mention);
            }
            if (isset($data['parcours'])) {
                $parcours = $this->parcoursRepository->find($data['parcours']);
                if (!$parcours && $data['parcours'] !== null) {
                    return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_BAD_REQUEST);
                }
                $agenda->setParcours($parcours);
            }
            if (isset($data['niveau'])) {
                $niveau = $this->niveauRepository->find($data['niveau']);
                if (!$niveau) {
                    return $this->json(['message' => 'Niveau non trouvé'], Response::HTTP_BAD_REQUEST);
                }
                $agenda->setNiveau($niveau);
            }
        }

        try {
            $this->entityManager->persist($agenda);
            $this->entityManager->flush();

            $this->logger->info('Agenda modifié avec succès', ['id' => $agenda->getId()]);

            return $this->json([
                'message' => 'Agenda modifié avec succès',
                '@id' => '/api/agenda/admin/' . $agenda->getId(),
                'titre' => $agenda->getTitre(),
                'description' => $agenda->getDescription(),
                'date' => $agenda->getDate()->format('c'),
                'dateExpiration' => $agenda->getDateExpiration()->format('c'),
                'type' => $agenda->getType(),
                'image' => $agenda->getImage() ? '/uploads/agenda/images/' . $agenda->getImage() : null,
                'video' => $agenda->getVideo() ? '/uploads/agenda/videos/' . $agenda->getVideo() : null,
                'url' => $agenda->getUrl(),
                'nomAuteur' => $agenda->getNomAuteur(),
                'mentionName' => $agenda->getMention() ? $agenda->getMention()->getName() : null,
                'parcoursName' => $agenda->getParcours() ? $agenda->getParcours()->getName() : null,
                'niveauNom' => $agenda->getNiveau() ? $agenda->getNiveau()->getNom() : null,
                'mentionId' => $agenda->getMention() ? $agenda->getMention()->getId() : null,
                'niveauId' => $agenda->getNiveau() ? $agenda->getNiveau()->getId() : null,
                'parcoursId' => $agenda->getParcours() ? $agenda->getParcours()->getId() : null,
            ], Response::HTTP_OK, [], ['groups' => ['agenda:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'agenda', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/admin/{id}/upload", name="api_upload_agenda_file", methods={"POST"})
     */
    public function uploadAgendaFile(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $agenda = $this->agendaRepository->find($id);
        if (!$agenda) {
            $this->logger->warning('Agenda non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Agenda non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($agenda->getType() === Agenda::TYPE_EXAMEN) {
            $this->logger->warning('Modification de fichiers non autorisée pour les examens');
            return $this->json(['message' => 'Modification de fichiers non autorisée pour les examens'], Response::HTTP_FORBIDDEN);
        }

        $imageFile = $request->files->get('image');
        $videoFile = $request->files->get('video');

        if (!$imageFile && !$videoFile) {
            $this->logger->warning('Aucun fichier fourni');
            return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($imageFile) {
                $mimeType = $imageFile->getMimeType();
                $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($mimeType, $validImageMimeTypes)) {
                    $this->logger->warning('Type de fichier image non supporté', ['mime_type' => $mimeType]);
                    return $this->json(['message' => 'Type de fichier image non supporté'], Response::HTTP_BAD_REQUEST);
                }

                if ($agenda->getImage()) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $agenda->getImage();
                    if (file_exists($oldImagePath)) {
                        if (unlink($oldImagePath)) {
                            $this->logger->info('Ancienne image supprimée', ['image' => $agenda->getImage()]);
                        } else {
                            $this->logger->warning('Échec de la suppression de l\'ancienne image', ['image' => $agenda->getImage()]);
                        }
                    }
                }

                $imageName = uniqid() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images', $imageName);
                $agenda->setImage($imageName);
                $agenda->setImageFile($imageFile);
                $this->logger->info('Nouvelle image enregistrée', ['image' => $imageName]);
            }

            if ($videoFile) {
                $mimeType = $videoFile->getMimeType();
                $validVideoMimeTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
                if (!in_array($mimeType, $validVideoMimeTypes)) {
                    $this->logger->warning('Type de fichier vidéo non supporté', ['mime_type' => $mimeType]);
                    return $this->json(['message' => 'Type de fichier vidéo non supporté'], Response::HTTP_BAD_REQUEST);
                }

                if ($agenda->getVideo()) {
                    $oldVideoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/videos/' . $agenda->getVideo();
                    if (file_exists($oldVideoPath)) {
                        if (unlink($oldVideoPath)) {
                            $this->logger->info('Ancienne vidéo supprimée', ['video' => $agenda->getVideo()]);
                        } else {
                            $this->logger->warning('Échec de la suppression de l\'ancienne vidéo', ['video' => $agenda->getVideo()]);
                        }
                    }
                }

                $videoName = uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $videoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/agenda/videos', $videoName);
                $agenda->setVideo($videoName);
                $agenda->setVideoFile($videoFile);
                $this->logger->info('Nouvelle vidéo enregistrée', ['video' => $videoName]);
            }

            $this->entityManager->persist($agenda);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Fichier(s) modifié(s) avec succès',
                '@id' => '/api/agenda/admin/' . $agenda->getId(),
                'image' => $agenda->getImage() ? '/uploads/agenda/images/' . $agenda->getImage() : null,
                'video' => $agenda->getVideo() ? '/uploads/agenda/videos/' . $agenda->getVideo() : null,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement des fichiers', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors du traitement des fichiers : ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/admin/{id}", name="api_delete_agenda", methods={"DELETE"})
     */
    public function deleteAgendaItem(int $id): Response
    {
        if (!is_numeric($id)) {
        $this->logger->warning('ID invalide', ['id' => $id]);
        return $this->json(['message' => 'ID invalide'], Response::HTTP_BAD_REQUEST);
    }

    $id = (int)$id;
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non autorisé', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $agenda = $this->agendaRepository->find($id);
        if (!$agenda) {
            $this->logger->warning('Agenda non trouvé', ['id' => $id]);
            return $this->json(['message' => 'Agenda non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($agenda->getType() === Agenda::TYPE_EXAMEN) {
            $this->logger->warning('Suppression d\'examen non autorisée');
            return $this->json(['message' => 'Suppression d\'examen non autorisée'], Response::HTTP_FORBIDDEN);
        }

        if ($agenda->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/images/' . $agenda->getImage();
            if (file_exists($imagePath)) {
                if (unlink($imagePath)) {
                    $this->logger->info('Image supprimée', ['image' => $agenda->getImage()]);
                } else {
                    $this->logger->warning('Échec de la suppression de l\'image', ['image' => $agenda->getImage()]);
                }
            }
        }

        if ($agenda->getVideo()) {
            $videoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/agenda/videos/' . $agenda->getVideo();
            if (file_exists($videoPath)) {
                if (unlink($videoPath)) {
                    $this->logger->info('Vidéo supprimée', ['video' => $agenda->getVideo()]);
                } else {
                    $this->logger->warning('Échec de la suppression de la vidéo', ['video' => $agenda->getVideo()]);
                }
            }
        }

        try {
            $this->entityManager->remove($agenda);
            $this->entityManager->flush();

            $this->logger->info('Agenda supprimé avec succès', ['id' => $id]);
            return $this->json(['message' => 'Agenda supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de l\'agenda', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}