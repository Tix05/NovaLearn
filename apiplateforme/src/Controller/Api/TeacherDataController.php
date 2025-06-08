<?php

namespace App\Controller\Api;

use App\Entity\Prof;
use App\Entity\FichierSupport;
use App\Entity\Ec;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Repository\MentionRepository;
use App\Repository\EcRepository;
use App\Repository\FichierSupportRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/teacher")
 */
class TeacherDataController extends AbstractController
{
    private $security;
    private $mentionRepository;
    private $entityManager;
    private $logger;

    public function __construct(
        Security $security,
        MentionRepository $mentionRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->mentionRepository = $mentionRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @Route("/mentions", name="api_teacher_mentions", methods={"GET"})
     */
    public function getTeacherMentions(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $prof = $this->entityManager->getRepository(Prof::class)
            ->findOneBy(['user' => $user]);

        if (!$prof) {
            return $this->json(['message' => 'Aucune donnée enseignant trouvée'], Response::HTTP_NOT_FOUND);
        }

        $mentions = [];
        $ecs = $prof->getEcs();

        foreach ($ecs as $ec) {
            $ue = $ec->getUe();
            $mention = $ue->getMention();
            $niveau = $ue->getSemestre()->getNiveau();

            $mentionId = $mention->getId();
            if (!isset($mentions[$mentionId])) {
                $mentions[$mentionId] = $this->buildMentionStructure($mention, $niveau, $prof);
            } else {
                $existingParcours = $mentions[$mentionId]['parcours'];
                $newParcours = $mention->getParcours()->map(fn($p) => $p->getName())->toArray();
                $mentions[$mentionId]['parcours'] = array_unique(array_merge($existingParcours, $newParcours));
            }
        }

        return $this->json(array_values($mentions));
    }

    private function buildMentionStructure($mention, $niveau, $prof)
    {
        $semestresData = [];
        $baseUrl = $this->getParameter('app.base_url');

        foreach ($mention->getUes() as $ue) {
            if ($ue->getSemestre() && $ue->getSemestre()->getNiveau()->getId() === $niveau->getId()) {
                $semestreId = $ue->getSemestre()->getId();

                if (!isset($semestresData[$semestreId])) {
                    $semestresData[$semestreId] = [
                        'id' => $semestreId,
                        'intitule' => $ue->getSemestre()->getName(),
                        'ues' => []
                    ];
                }

                $ueData = [
                    'id' => $ue->getId(),
                    'nom' => $ue->getName(),
                    'cours' => []
                ];

                foreach ($ue->getEcs() as $ec) {
                    if ($ec->getProf()->getId() === $prof->getId()) {
                        $ueData['cours'][] = [
                            'id' => $ec->getId(),
                            'titre' => $ec->getName(),
                            'credit' => $ec->getCoeff(),
                            'description' => $ec->getDescription() ?? 'Aucune description disponible',
                            'supports' => $this->getFormattedSupports($ec->getFichierSupports())
                        ];
                    }
                }

                if (!empty($ueData['cours'])) {
                    $semestresData[$semestreId]['ues'][] = $ueData;
                }
            }
        }

        $iconUrl = $mention->getIcon() ? $baseUrl . '/Uploads/icons/' . $mention->getIcon() : null;
        $parcours = $mention->getParcours()->map(fn($p) => $p->getName())->toArray();

        return [
            'id' => $mention->getId(),
            'nom' => $mention->getName(),
            'niveau' => $niveau->getNom(),
            'parcours' => $parcours ?: ['Non spécifié'],
            'icon' => $iconUrl,
            'semestres' => array_values($semestresData)
        ];
    }

    private function getFormattedSupports($supports)
    {
        $formatted = [];
        $baseUrl = $this->getParameter('app.base_url');

        foreach ($supports as $support) {
            $fileUrl = $support->getFichier()
                ? $baseUrl . '/uploads/supports/' . $support->getFichier()
                : $support->getUrl();

            $formatted[] = [
                'id' => $support->getId(),
                'type' => $this->mapSupportType($support->getType()),
                'titre' => $support->getTitre(),
                'description' => $support->getDescription(),
                'url' => $fileUrl,
                'fichier' => $support->getFichier(),
                'date_ajout' => $support->getDateAjout()->format('Y-m-d H:i:s'),
                'estPublique' => $support->isEstPublique()
            ];
        }

        return $formatted;
    }

    private function mapSupportType($type)
    {
        $types = [
            FichierSupport::TYPE_FICHIER => 'document',
            FichierSupport::TYPE_VIDEO => 'video',
            FichierSupport::TYPE_AUDIO => 'audio',
            FichierSupport::TYPE_LIEN => 'lien'
        ];

        $type = str_replace("'", "", $type);

        return $types[$type] ?? 'document';
    }

    /**
     * @Route("/supports", name="api_teacher_add_support", methods={"POST"})
     */
    public function addSupport(Request $request, EntityManagerInterface $entityManager, EcRepository $ecRepository, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        $logger->info('Utilisateur authentifié', ['user' => $user ? $user->getEmail() : null, 'roles' => $user ? $user->getRoles() : null]);

        if (!$user instanceof User) {
            $logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $logger->error('Utilisateur non enseignant', ['user' => $user->getEmail(), 'roles' => $user->getRoles()]);
            return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_FORBIDDEN);
        }

        $prof = $entityManager->getRepository(Prof::class)->findOneBy(['user' => $user->getId()]);
        if (!$prof) {
            $logger->error('Aucune entité Prof trouvée pour cet utilisateur', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Utilisateur non associé à un profil enseignant'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->all();
        $ecId = $data['ec_id'] ?? null;
        $titre = $data['titre'] ?? null;
        $type = $data['type'] ?? null;
        $url = $data['url'] ?? null;
        $mimeType = $data['mimeType'] ?? null;

        if (!$ecId || !$titre || !$type) {
            $logger->warning('Données manquantes', ['ec_id' => $ecId, 'titre' => $titre, 'type' => $type]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $ecRepository->find($ecId);
        if (!$ec) {
            $logger->warning('EC non trouvé', ['ec_id' => $ecId]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ec->getProf()->getId() !== $prof->getId()) {
            $logger->warning('Non autorisé à ajouter un support pour cet EC', ['ec_id' => $ecId, 'prof_id' => $prof->getId()]);
            return $this->json(['message' => 'Non autorisé à ajouter un support pour cet EC'], Response::HTTP_FORBIDDEN);
        }

        $support = new FichierSupport();
        $support->setTitre($titre);
        $support->setType($type);
        $support->setEc($ec);
        $support->setDateAjout(new \DateTime());
        $support->setAuteur($user);
        $support->setEstPublique(true);

        if ($type === 'LIEN') {
            if (!$url) {
                $logger->warning('URL manquante pour un support de type LIEN');
                return $this->json(['message' => 'URL requise pour un support de type LIEN'], Response::HTTP_BAD_REQUEST);
            }
            $support->setUrl($url);
        } else {
            $file = $request->files->get('fichier');
            if (!$file) {
                $logger->warning('Aucun fichier fourni pour un support de type ' . $type);
                return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
            }

            $validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            $validAudioTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];
            if ($type === 'VIDEO' && !in_array($mimeType, $validVideoTypes)) {
                $logger->warning('Type de fichier vidéo non supporté', ['mimeType' => $mimeType]);
                return $this->json(['message' => 'Format vidéo non supporté. Formats acceptés : MP4, WebM, OGG'], Response::HTTP_BAD_REQUEST);
            }
            if ($type === 'AUDIO' && !in_array($mimeType, $validAudioTypes)) {
                $logger->warning('Type de fichier audio non supporté', ['mimeType' => $mimeType]);
                return $this->json(['message' => 'Format audio non supporté. Formats acceptés : MP3, WAV, OGG'], Response::HTTP_BAD_REQUEST);
            }

            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $this->getParameter('supports_directory') . '/' . $fileName;
            $file->move($this->getParameter('supports_directory'), $fileName);

            $support->setFichier($fileName);
        }

        $entityManager->persist($support);
        $entityManager->flush();

        $logger->info('Support ajouté avec succès', ['support_id' => $support->getId(), 'titre' => $titre]);

        return $this->json([
            'message' => 'Support ajouté avec succès',
            'support' => [
                'id' => $support->getId(),
                'titre' => $support->getTitre(),
                'type' => $this->mapSupportType($support->getType()),
                'url' => $support->getUrl(),
                'fichier' => $support->getFichier(),
                'date_ajout' => $support->getDateAjout()->format('Y-m-d H:i:s'),
                'estPublique' => $support->isEstPublique()
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/supports/{id}", name="api_teacher_delete_support", methods={"DELETE"})
     */
    public function deleteSupport(int $id, FichierSupportRepository $supportRepository, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        $logger->info('Utilisateur authentifié', ['user' => $user ? $user->getEmail() : null, 'roles' => $user ? $user->getRoles() : null]);

        if (!$user instanceof User) {
            $logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $logger->error('Utilisateur non enseignant', ['user' => $user->getEmail(), 'roles' => $user->getRoles()]);
            return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_FORBIDDEN);
        }

        $prof = $entityManager->getRepository(Prof::class)->findOneBy(['user' => $user->getId()]);
        if (!$prof) {
            $logger->error('Aucune entité Prof trouvée pour cet utilisateur', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Utilisateur non associé à un profil enseignant'], Response::HTTP_FORBIDDEN);
        }

        $support = $supportRepository->find($id);
        if (!$support) {
            $logger->warning('Support non trouvé', ['support_id' => $id]);
            return $this->json(['message' => 'Support non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $ec = $support->getEc();
        if ($ec->getProf()->getId() !== $prof->getId()) {
            $logger->warning('Non autorisé à supprimer ce support', ['support_id' => $id, 'prof_id' => $prof->getId()]);
            return $this->json(['message' => 'Non autorisé à supprimer ce support'], Response::HTTP_FORBIDDEN);
        }

        if ($support->getFichier()) {
            $filePath = $this->getParameter('supports_directory') . '/' . $support->getFichier();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $entityManager->remove($support);
        $entityManager->flush();

        $logger->info('Support supprimé avec succès', ['support_id' => $id]);

        return $this->json(['message' => 'Support supprimé avec succès'], Response::HTTP_OK);
    }

    /**
     * @Route("/ecs/{id}/description", name="api_teacher_update_ec_description", methods={"PUT"})
     */
    public function updateEcDescription(int $id, Request $request, EntityManagerInterface $entityManager, EcRepository $ecRepository, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        $logger->info('Utilisateur authentifié pour mise à jour description EC', ['user' => $user ? $user->getEmail() : null, 'roles' => $user ? $user->getRoles() : null]);

        if (!$user instanceof User) {
            $logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $logger->error('Utilisateur non enseignant', ['user' => $user->getEmail(), 'roles' => $user->getRoles()]);
            return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_FORBIDDEN);
        }

        $prof = $entityManager->getRepository(Prof::class)->findOneBy(['user' => $user->getId()]);
        if (!$prof) {
            $logger->error('Aucune entité Prof trouvée pour cet utilisateur', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Utilisateur non associé à un profil enseignant'], Response::HTTP_FORBIDDEN);
        }

        $ec = $ecRepository->find($id);
        if (!$ec) {
            $logger->warning('EC non trouvé', ['ec_id' => $id]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ec->getProf()->getId() !== $prof->getId()) {
            $logger->warning('Non autorisé à modifier cet EC', ['ec_id' => $id, 'prof_id' => $prof->getId()]);
            return $this->json(['message' => 'Non autorisé à modifier cet EC'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $description = $data['description'] ?? null;

        if ($description === null) {
            $logger->warning('Description manquante', ['ec_id' => $id]);
            return $this->json(['message' => 'Description manquante'], Response::HTTP_BAD_REQUEST);
        }

        // Nettoyer la description pour éviter les problèmes de sécurité
        $description = strip_tags($description, '<p><strong><em><u><ol><ul><li><h1><h2><h3><blockquote><br><span>');

        $ec->setDescription($description);
        $entityManager->persist($ec);
        $entityManager->flush();

        $logger->info('Description EC mise à jour avec succès', ['ec_id' => $id, 'description' => substr($description, 0, 100) . '...']);

        return $this->json([
            'message' => 'Description mise à jour avec succès',
            'description' => $ec->getDescription()
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/ec/{id}/supports", name="api_ec_supports", methods={"GET"})
     */
    public function getEcSupports(int $id, EcRepository $ecRepository, LoggerInterface $logger): JsonResponse
    {
        $user = $this->getUser();
        $logger->info('Utilisateur authentifié pour récupération des supports', [
            'user' => $user ? $user->getEmail() : null,
            'ec_id' => $id
        ]);

        if (!$user instanceof User) {
            $logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $logger->error('Utilisateur non enseignant', [
                'user' => $user->getEmail(),
                'roles' => $user->getRoles()
            ]);
            return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_FORBIDDEN);
        }

        $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user->getId()]);
        if (!$prof) {
            $logger->error('Aucune entité Prof trouvée pour cet utilisateur', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Utilisateur non associé à un profil enseignant'], Response::HTTP_FORBIDDEN);
        }

        $ec = $ecRepository->find($id);
        if (!$ec) {
            $logger->warning('EC non trouvé', ['ec_id' => $id]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ec->getProf()->getId() !== $prof->getId()) {
            $logger->warning('Non autorisé à accéder aux supports de cet EC', [
                'ec_id' => $id,
                'prof_id' => $prof->getId()
            ]);
            return $this->json(['message' => 'Non autorisé à accéder aux supports de cet EC'], Response::HTTP_FORBIDDEN);
        }

        $supports = $this->getFormattedSupports($ec->getFichierSupports());
        return $this->json($supports);
    }


    /**
     * Récupère les détails d'un cours (EC) avec les commentaires
     * @Route("/mentions/{mentionId}/semestres/{semestreId}/cours/{coursId}", name="api_teacher_cours_details", methods={"GET"})
     */
    public function getCoursDetails(
        int $mentionId,
        string $semestreId,
        int $coursId,
        EcRepository $ecRepository,
        CommentaireRepository $commentaireRepository
    ): JsonResponse {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Utilisateur non authentifié', ['user' => $user ? $user->getEmail() : null]);
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);
        if (!$prof) {
            $this->logger->warning('Aucune donnée enseignant trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée enseignant trouvée'], Response::HTTP_NOT_FOUND);
        }

        $ec = $ecRepository->find($coursId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $coursId]);
            return $this->json(['message' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ec->getProf()->getId() !== $prof->getId()) {
            $this->logger->warning('Non autorisé à accéder à cet EC', ['ec_id' => $coursId, 'prof_id' => $prof->getId()]);
            return $this->json(['message' => 'Non autorisé à accéder à ce cours'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier si l'EC appartient à la mention et au semestre
        $mention = $ec->getUe()->getMention();
        if ($mention->getId() != $mentionId || $ec->getUe()->getSemestre()->getId() != $semestreId) {
            $this->logger->warning('Cours non associé à la mention ou semestre', [
                'ec_id' => $coursId,
                'mention_id' => $mentionId,
                'semestre_id' => $semestreId
            ]);
            return $this->json(['message' => 'Cours non associé à la mention ou semestre'], Response::HTTP_FORBIDDEN);
        }

        // Récupérer les commentaires de premier niveau (sans parent)
        $commentaires = $commentaireRepository->findBy(['ec' => $ec, 'parent' => null], ['time' => 'DESC']);
        $commentairesData = array_map(function ($commentaire) use ($commentaireRepository, $user) {
            $replies = array_filter($commentaire->getChildren()->toArray(), function ($reply) use ($commentaire) {
                return $reply->getId() !== $commentaire->getId();
            });

            $avatar = $commentaire->getUser()->getAvatar();
            $avatarUrl = $avatar ? $this->getParameter('app.base_url') . '/Uploads/avatars/' . $avatar : null;
            $authorName = $commentaire->getUser()->getId() === $user->getId() ? 'Moi' : $commentaire->getUser()->getName();

            return [
                'id' => $commentaire->getId(),
                'author' => $authorName,
                'avatar' => $avatarUrl ?? $this->getInitials($authorName),
                'content' => $commentaire->getContenu(),
                'date' => $commentaire->getTime()->format('Y-m-d'),
                'isOwner' => $commentaire->getUser()->getId() === $user->getId(),
                'replies' => array_map(function ($reply) use ($user) {
                    $replyAvatar = $reply->getUser()->getAvatar();
                    $replyAvatarUrl = $replyAvatar ? $this->getParameter('app.base_url') . '/Uploads/avatars/' . $replyAvatar : null;
                    $replyAuthorName = $reply->getUser()->getId() === $user->getId() ? 'Moi' : $reply->getUser()->getName();
                    return [
                        'id' => $reply->getId(),
                        'author' => $replyAuthorName,
                        'avatar' => $replyAvatarUrl ?? $this->getInitials($replyAuthorName),
                        'content' => $reply->getContenu(),
                        'date' => $reply->getTime()->format('Y-m-d'),
                        'isOwner' => $reply->getUser()->getId() === $user->getId()
                    ];
                }, $replies)
            ];
        }, $commentaires);

        return $this->json([
            'titre' => $ec->getName(),
            'description' => $ec->getDescription() ?? 'Aucune description disponible',
            'supports' => $this->getFormattedSupports($ec->getFichierSupports()->toArray()),
            'commentaires' => $commentairesData
        ]);
    }

    /**
     * Ajoute un commentaire ou une réponse à un cours
     * @Route("/commentaires", name="api_teacher_add_commentaire", methods={"POST"})
     */
    public function addCommentaire(
        Request $request,
        EcRepository $ecRepository,
        CommentaireRepository $commentaireRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Utilisateur non authentifié', ['user' => $user ? $user->getEmail() : null]);
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);
        if (!$prof) {
            $this->logger->warning('Aucune donnée enseignant trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée enseignant trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['coursId'], $data['contenu'])) {
            $this->logger->warning('Données manquantes', ['data' => $data]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $ecRepository->find($data['coursId']);
        if (!$ec) {
            $this->logger->warning('Cours non trouvé', ['ec_id' => $data['coursId']]);
            return $this->json(['message' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ec->getProf()->getId() !== $prof->getId()) {
            $this->logger->warning('Non autorisé à commenter cet EC', ['ec_id' => $data['coursId'], 'prof_id' => $prof->getId()]);
            return $this->json(['message' => 'Non autorisé à commenter ce cours'], Response::HTTP_FORBIDDEN);
        }

        $commentaire = new Commentaire();
        $commentaire->setContenu($data['contenu']);
        $commentaire->setUser($user);
        $commentaire->setEc($ec);
        $commentaire->setStatus(true);

        if (!empty($data['parentId'])) {
            $parent = $commentaireRepository->find($data['parentId']);
            if (!$parent) {
                $this->logger->warning('Commentaire parent non trouvé', ['parent_id' => $data['parentId']]);
                return $this->json(['message' => 'Commentaire parent non trouvé'], Response::HTTP_NOT_FOUND);
            }
            $commentaire->setParent($parent);
        }

        $errors = $validator->validate($commentaire);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            $this->logger->warning('Validation échouée', ['errors' => $errorMessages]);
            return $this->json(['message' => 'Validation échouée', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();

        $this->logger->info('Commentaire ajouté avec succès', ['commentaire_id' => $commentaire->getId(), 'user_id' => $user->getId()]);

        $avatar = $user->getAvatar();
        $avatarUrl = $avatar ? $this->getParameter('app.base_url') . '/Uploads/avatars/' . $avatar : null;

        return $this->json([
            'id' => $commentaire->getId(),
            'author' => 'Moi',
            'avatar' => $avatarUrl ?? $this->getInitials('Moi'),
            'content' => $commentaire->getContenu(),
            'date' => $commentaire->getTime()->format('Y-m-d'),
            'isOwner' => true
        ], Response::HTTP_CREATED);
    }

    /**
     * Met à jour un commentaire
     * @Route("/commentaires/{id}", name="api_teacher_update_commentaire", methods={"PUT"})
     */
    public function updateCommentaire(
        int $id,
        Request $request,
        CommentaireRepository $commentaireRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Utilisateur non authentifié', ['user' => $user ? $user->getEmail() : null]);
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);
        if (!$prof) {
            $this->logger->warning('Aucune donnée enseignant trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée enseignant trouvée'], Response::HTTP_NOT_FOUND);
        }

        $commentaire = $commentaireRepository->find($id);
        if (!$commentaire) {
            $this->logger->warning('Commentaire non trouvé', ['commentaire_id' => $id]);
            return $this->json(['message' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($commentaire->getUser()->getId() !== $user->getId()) {
            $this->logger->warning('Tentative de mise à jour non autorisée', [
                'commentaire_id' => $id,
                'user_id' => $user->getId()
            ]);
            return $this->json(['message' => 'Vous n\'êtes pas autorisé à modifier ce commentaire'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['contenu'])) {
            $this->logger->warning('Données manquantes', ['data' => $data]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $commentaire->setContenu($data['contenu']);
        $commentaire->setTime(new \DateTime());

        $errors = $validator->validate($commentaire);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            $this->logger->warning('Validation échouée', ['errors' => $errorMessages]);
            return $this->json(['message' => 'Validation échouée', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $this->logger->info('Commentaire mis à jour avec succès', ['commentaire_id' => $id, 'user_id' => $user->getId()]);

        $avatar = $user->getAvatar();
        $avatarUrl = $avatar ? $this->getParameter('app.base_url') . '/Uploads/avatars/' . $avatar : null;

        return $this->json([
            'id' => $commentaire->getId(),
            'author' => 'Moi',
            'avatar' => $avatarUrl ?? $this->getInitials('Moi'),
            'content' => $commentaire->getContenu(),
            'date' => $commentaire->getTime()->format('Y-m-d'),
            'isOwner' => true
        ]);
    }

    /**
     * Supprime un commentaire
     * @Route("/commentaires/{id}", name="api_teacher_delete_commentaire", methods={"DELETE"})
     */
    public function deleteCommentaire(
        int $id,
        CommentaireRepository $commentaireRepository
    ): JsonResponse {
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Utilisateur non authentifié', ['user' => $user ? $user->getEmail() : null]);
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);
        if (!$prof) {
            $this->logger->warning('Aucune donnée enseignant trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée enseignant trouvée'], Response::HTTP_NOT_FOUND);
        }

        $commentaire = $commentaireRepository->find($id);
        if (!$commentaire) {
            $this->logger->warning('Commentaire non trouvé', ['commentaire_id' => $id]);
            return $this->json(['message' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($commentaire->getUser()->getId() !== $user->getId()) {
            $this->logger->warning('Tentative de suppression non autorisée', [
                'commentaire_id' => $id,
                'user_id' => $user->getId()
            ]);
            return $this->json(['message' => 'Vous n\'êtes pas autorisé à supprimer ce commentaire'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($commentaire);
        $this->entityManager->flush();

        $this->logger->info('Commentaire supprimé avec succès', ['commentaire_id' => $id, 'user_id' => $user->getId()]);

        return $this->json(['message' => 'Commentaire supprimé avec succès']);
    }

    private function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) {
                    break;
                }
            }
        }
        return $initials ?: 'U';
    }
}
