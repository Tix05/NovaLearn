<?php

namespace App\Controller\Api;

use App\Entity\FichierSupport;
use App\Entity\Ec;
use App\Entity\User;
use App\Entity\Mention;
use App\Entity\Commentaire;
use App\Repository\MentionRepository;
use App\Repository\EcRepository;
use App\Repository\FichierSupportRepository;
use App\Repository\UserRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/admin")
 */
class AdminDataController extends AbstractController
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
     * Récupère toutes les mentions
     * @Route("/mentions", name="api_admin_mentions", methods={"GET"})
     */
    public function getMentions(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $mentions = $this->mentionRepository->findAll();
        $baseUrl = $this->getParameter('app.base_url');

        $mentionsData = array_map(function ($mention) use ($baseUrl) {
            return [
                'id' => $mention->getId(),
                'nom' => $mention->getName(),
                'icon' => $mention->getIcon() ? $baseUrl . '/uploads/icons/' . $mention->getIcon() : null,
            ];
        }, $mentions);

        return $this->json($mentionsData);
    }

    /**
 * Récupère les niveaux et parcours pour une mention
 * @Route("/mentions/{mentionId}/niveaux", name="api_admin_niveaux", methods={"GET"})
 */
public function getNiveaux(int $mentionId): JsonResponse
{
    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
        $this->logger->error('Utilisateur non authentifié ou non administrateur', [
            'user' => $user ? $user->getEmail() : null,
            'roles' => $user ? $user->getRoles() : null
        ]);
        return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    $mention = $this->mentionRepository->find($mentionId);
    if (!$mention) {
        $this->logger->warning('Mention non trouvée', ['mention_id' => $mentionId]);
        return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $niveauxData = [];
    $parcoursRepo = $this->entityManager->getRepository(\App\Entity\Parcours::class);
    $niveauRepo = $this->entityManager->getRepository(\App\Entity\Niveau::class);
    $semestreRepo = $this->entityManager->getRepository(\App\Entity\Semestre::class);

    $parcours = $parcoursRepo->findBy(['mention' => $mention]);
    foreach ($parcours as $parcour) {
        $niveau = $parcour->getNiveau();
        $niveauId = $niveau->getId();
        if (!isset($niveauxData[$niveauId])) {
            $semestres = $semestreRepo->findBy(['niveau' => $niveau]);
            $semestresData = array_map(function ($semestre) {
                return [
                    'id' => $semestre->getId(),
                    'name' => $semestre->getName(), // Inclure le nom du semestre
                ];
            }, $semestres);

            $niveauxData[$niveauId] = [
                'id' => $niveau->getId(),
                'nom' => $niveau->getNom(),
                'parcours' => [[
                    'id' => $parcour->getId(),
                    'name' => $parcour->getName(),
                ]],
                'semestres' => $semestresData,
            ];
        } else {
            $niveauxData[$niveauId]['parcours'][] = [
                'id' => $parcour->getId(),
                'name' => $parcour->getName(),
            ];
        }
    }

    return $this->json(array_values($niveauxData));
}

    /**
     * Récupère les cours (UEs et ECs) pour un semestre
     * @Route("/mentions/{mentionId}/niveaux/{niveauId}/semestres/{semestreId}/cours", name="api_admin_cours", methods={"GET"})
     */
    public function getCours(int $mentionId, string $niveauId, string $semestreId): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $mention = $this->mentionRepository->find($mentionId);
        if (!$mention) {
            $this->logger->warning('Mention non trouvée', ['mention_id' => $mentionId]);
            return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $niveau = $this->entityManager->getRepository(\App\Entity\Niveau::class)->find($niveauId);
        if (!$niveau) {
            $this->logger->warning('Niveau non trouvé', ['niveau_id' => $niveauId]);
            return $this->json(['message' => 'Niveau non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $semestre = $this->entityManager->getRepository(\App\Entity\Semestre::class)->find($semestreId);
        if (!$semestre) {
            $this->logger->warning('Semestre non trouvé', ['semestre_id' => $semestreId]);
            return $this->json(['message' => 'Semestre non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $ues = $this->entityManager->getRepository(\App\Entity\Ue::class)->findBy(['semestre' => $semestre, 'mention' => $mention]);
        $uesData = [];

        foreach ($ues as $ue) {
            $ueData = [
                'id' => $ue->getId(),
                'nom' => $ue->getName(),
                'cours' => []
            ];

            foreach ($ue->getEcs() as $ec) {
                $ueData['cours'][] = [
                    'id' => $ec->getId(),
                    'titre' => $ec->getName(),
                    'credit' => $ec->getCoeff(),
                    'description' => $ec->getDescription() ?? 'Aucune description disponible',
                    'supports' => $this->getFormattedSupports($ec->getFichierSupports())
                ];
            }

            if (!empty($ueData['cours'])) {
                $uesData[] = $ueData;
            }
        }

        return $this->json([
            'mention' => ['id' => $mention->getId(), 'nom' => $mention->getName()],
            'niveau' => ['id' => $niveau->getId(), 'nom' => $niveau->getNom()],
            'semestre' => ['id' => $semestre->getId(), 'intitule' => $semestre->getName()],
            'ues' => $uesData
        ]);
    }

    /**
     * Récupère les détails d'un cours (EC)
     * @Route("/mentions/{mentionId}/niveaux/{niveauId}/semestres/{semestreId}/cours/{coursId}", name="api_admin_cours_details", methods={"GET"})
     */
    public function getCoursDetails(int $mentionId, string $niveauId, string $semestreId, int $coursId, CommentaireRepository $commentaireRepository): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $ec = $this->entityManager->getRepository(Ec::class)->find($coursId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $coursId]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les commentaires de premier niveau (sans parent)
        $commentaires = $commentaireRepository->findBy(['ec' => $ec, 'parent' => null]);
        $commentairesData = array_map(function ($commentaire) use ($commentaireRepository) {
            // Récupérer les réponses via la relation children, en évitant les boucles
            $replies = array_filter($commentaire->getChildren()->toArray(), function ($reply) use ($commentaire) {
                return $reply->getId() !== $commentaire->getId(); // Exclure les références circulaires
            });

            // Construire l'URL complète pour l'avatar
            $avatar = $commentaire->getUser()->getAvatar();
            $avatarUrl = $avatar ? '/uploads/avatars/' . $avatar : null;

            return [
                'id' => $commentaire->getId(),
                'author' => $commentaire->getUser()->getName(),
                'avatar' => $avatarUrl ?? getInitials($commentaire->getUser()->getName()),
                'content' => $commentaire->getContenu(),
                'date' => $commentaire->getTime()->format('Y-m-d'),
                'replies' => array_map(function ($reply) {
                    $replyAvatar = $reply->getUser()->getAvatar();
                    $replyAvatarUrl = $replyAvatar ? '/uploads/avatars/' . $replyAvatar : null;

                    return [
                        'id' => $reply->getId(),
                        'author' => $reply->getUser()->getName(),
                        'avatar' => $replyAvatarUrl ?? getInitials($reply->getUser()->getName()),
                        'content' => $reply->getContenu(),
                        'date' => $reply->getTime()->format('Y-m-d')
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
     * Ajoute un support pour un EC
     * @Route("/supports", name="api_admin_add_support", methods={"POST"})
     */
    public function addSupport(Request $request, EntityManagerInterface $entityManager, EcRepository $ecRepository): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->request->all();
        $data = $request->request->all();
        $ecId = $data['ec_id'] ?? null;
        $titre = $data['titre'] ?? null;
        $type = $data['type'] ?? null;
        $url = $data['url'] ?? null;
        $mimeType = $data['mimeType'] ?? null;

        if (!$ecId || !$titre || !$type) {
            $this->logger->warning('Données manquantes', ['ec_id' => $ecId, 'titre' => $titre, 'type' => $type]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $ecRepository->find($ecId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $ecId]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
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
                $this->logger->warning('URL manquante pour un support de type LIEN');
                return $this->json(['message' => 'URL requise pour un support de type LIEN'], Response::HTTP_BAD_REQUEST);
            }
            $support->setUrl($url);
        } else {
            $file = $request->files->get('fichier');
            if (!$file) {
                $this->logger->warning('Aucun fichier fourni pour un support de type ' . $type);
                return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
            }

            $validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            $validAudioTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];
            if ($type === 'VIDEO' && !in_array($mimeType, $validVideoTypes)) {
                $this->logger->warning('Type de fichier vidéo non supporté', ['mimeType' => $mimeType]);
                return $this->json(['message' => 'Format vidéo non supporté. Formats acceptés : MP4, WebM, OGG'], Response::HTTP_BAD_REQUEST);
            }
            if ($type === 'AUDIO' && !in_array($mimeType, $validAudioTypes)) {
                $this->logger->warning('Type de fichier audio non supporté', ['mimeType' => $mimeType]);
                return $this->json(['message' => 'Format audio non supporté. Formats acceptés : MP3, WAV, OGG'], Response::HTTP_BAD_REQUEST);
            }

            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $this->getParameter('supports_directory') . '/' . $fileName;
            $file->move($this->getParameter('supports_directory'), $fileName);

            $support->setFichier($fileName);
        }

        $entityManager->persist($support);
        $entityManager->flush();

        $this->logger->info('Support ajouté avec succès', ['support_id' => $support->getId(), 'titre' => $titre]);

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
     * Supprime un support
     * @Route("/supports/{id}", name="api_admin_delete_support", methods={"DELETE"})
     */
    public function deleteSupport(int $id, FichierSupportRepository $supportRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $support = $supportRepository->find($id);
        if (!$support) {
            $this->logger->warning('Support non trouvé', ['support_id' => $id]);
            return $this->json(['message' => 'Support non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($support->getFichier()) {
            $filePath = $this->getParameter('supports_directory') . '/' . $support->getFichier();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $entityManager->remove($support);
        $entityManager->flush();

        $this->logger->info('Support supprimé avec succès', ['support_id' => $id]);

        return $this->json(['message' => 'Support supprimé avec succès'], Response::HTTP_OK);
    }

    /**
     * Met à jour la description d'un EC
     * @Route("/ecs/{id}/description", name="api_admin_update_ec_description", methods={"PUT"})
     */
    public function updateEcDescription(int $id, Request $request, EntityManagerInterface $entityManager, EcRepository $ecRepository): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->logger->error('Utilisateur non authentifié ou non administrateur', [
                'user' => $user ? $user->getEmail() : null,
                'roles' => $user ? $user->getRoles() : null
            ]);
            return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $ec = $ecRepository->find($id);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $id]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $description = $data['description'] ?? null;

        if ($description === null) {
            $this->logger->warning('Description manquante', ['ec_id' => $id]);
            return $this->json(['message' => 'Description manquante'], Response::HTTP_BAD_REQUEST);
        }

        $description = strip_tags($description, '<p><strong><em><u><ol><ul><li><h1><h2><h3><blockquote><br><span>');

        $ec->setDescription($description);
        $entityManager->persist($ec);
        $entityManager->flush();

        $this->logger->info('Description EC mise à jour avec succès', ['ec_id' => $id, 'description' => substr($description, 0, 100) . '...']);

        return $this->json([
            'message' => 'Description mise à jour avec succès',
            'description' => $ec->getDescription()
        ], Response::HTTP_OK);
    }

    /**
 * @Route("/commentaires/{id}", name="api_admin_delete_commentaire", methods={"DELETE"})
 */
public function deleteCommentaire(int $id, CommentaireRepository $commentaireRepository, EntityManagerInterface $entityManager): Response
{
    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
        $this->logger->error('Utilisateur non authentifié ou non administrateur', [
            'user' => $user ? $user->getEmail() : null,
            'roles' => $user ? $user->getRoles() : null
        ]);
        return $this->json(['message' => 'Utilisateur non authentifié ou non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    $commentaire = $commentaireRepository->find($id);
    if (!$commentaire) {
        $this->logger->warning('Commentaire non trouvé', ['commentaire_id' => $id]);
        return $this->json(['message' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
    }

    $this->deleteChildren($commentaire, $commentaireRepository);

    $entityManager->remove($commentaire);
    $entityManager->flush();

    $this->logger->info('Commentaire supprimé avec succès', ['commentaire_id' => $id]);

    return $this->json(['message' => 'Commentaire supprimé avec succès'], Response::HTTP_OK);
}

private function deleteChildren(Commentaire $commentaire, CommentaireRepository $commentaireRepository): void
{
    $children = $commentaire->getChildren();
    foreach ($children as $child) {
        $this->deleteChildren($child, $commentaireRepository);
        $this->entityManager->remove($child);
        $this->logger->info('Commentaire enfant supprimé', [
            'commentaire_id' => $child->getId(),
            'parent_id' => $commentaire->getId()
        ]);
    }
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

    function getInitials(string $name): string
{
    $nameParts = explode(' ', trim($name));
    if (count($nameParts) >= 2) {
        return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    }
    return strtoupper(substr($nameParts[0], 0, 2));
}
}