<?php

namespace App\Controller\Api;

use App\Entity\Commentaire;
use App\Entity\Ec;
use App\Entity\Etudiant;
use App\Entity\FichierSupport;
use App\Repository\CommentaireRepository;
use App\Repository\EcRepository;
use App\Repository\MentionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/student")
 */
class StudentDataController extends AbstractController
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
     * @Route("/mentions", name="api_student_mentions", methods={"GET"})
     */
    public function getStudentMentions(): Response
    {
        $user = $this->security->getUser();
        $etudiant = $user->getEtudiants()->first();
        
        if (!$etudiant) {
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], Response::HTTP_NOT_FOUND);
        }

        $mention = $etudiant->getMention();
        $parcours = $etudiant->getParcours();
        $niveau = $etudiant->getNiveau();

        // Construire la structure complète
        $mentionsData = $this->buildMentionStructure($mention, $parcours, $niveau, $etudiant);

        return $this->json($mentionsData);
    }

    private function buildMentionStructure($mention, $parcours, $niveau, $etudiant)
    {
        $semestresData = [];
        $baseUrl = $this->getParameter('app.base_url'); // Base URL from parameters
        
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
                    $ueData['cours'][] = [
                        'id' => $ec->getId(),
                        'titre' => $ec->getName(),
                        'credit' => $ec->getCoeff(),
                        'description' => $ec->getDescription() ?? 'Aucune description disponible',
                        'supports' => $this->getFormattedSupports($ec->getFichierSupports())
                    ];
                }
                
                $semestresData[$semestreId]['ues'][] = $ueData;
            }
        }

        // Construct full URL for the mention icon
        $iconUrl = $mention->getIcon() ? $baseUrl . '/uploads/icons/' . $mention->getIcon() : null;

        return [
            [
                'id' => $mention->getId(),
                'nom' => $mention->getName(),
                'niveau' => $niveau->getNom(),
                'parcours' => $parcours->getName(),
                'matricule' => $etudiant->getMatricule(),
                'icon' => $iconUrl, // Use full URL for the icon
                'semestres' => array_values($semestresData)
            ]
        ];
    }

    private function getFormattedSupports($supports)
    {
        $formatted = [];
        $baseUrl = $this->getParameter('app.base_url');

        foreach ($supports as $support) {
            if (!$support->isEstPublique()) {
                continue; // Ne pas inclure les supports non publics
            }

            $fileUrl = $support->getFichier() 
                ? $baseUrl . '/Uploads/supports/' . $support->getFichier() 
                : $support->getUrl();

            $formatted[] = [
                'id' => $support->getId(),
                'type' => $this->mapSupportType($support->getType()),
                'titre' => $support->getTitre(),
                'description' => $support->getDescription(),
                'url' => $fileUrl,
                'date_ajout' => $support->getDateAjout()->format('Y-m-d H:i:s')
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
     * Récupère les détails d'un cours (EC) avec les commentaires
     * @Route("/mentions/{mentionId}/semestres/{semestreId}/cours/{coursId}", name="api_student_cours_details", methods={"GET"})
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

        $etudiant = $user->getEtudiants()->first();
        if (!$etudiant) {
            $this->logger->warning('Aucune donnée étudiante trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], Response::HTTP_NOT_FOUND);
        }

        $ec = $ecRepository->find($coursId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $coursId]);
            return $this->json(['message' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'étudiant a accès à ce cours via sa mention
        $mention = $etudiant->getMention();
        if ($mention->getId() != $mentionId) {
            $this->logger->warning('Accès non autorisé à la mention', ['mention_id' => $mentionId, 'user_id' => $user->getId()]);
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Récupérer les commentaires de premier niveau (sans parent)
        $commentaires = $commentaireRepository->findBy(['ec' => $ec, 'parent' => null]);
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
     * @Route("/commentaires", name="api_student_add_commentaire", methods={"POST"})
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

        $etudiant = $user->getEtudiants()->first();
        if (!$etudiant) {
            $this->logger->warning('Aucune donnée étudiante trouvée', ['user_id' => $user->getId()]);
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['coursId'], $data['contenu'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $ecRepository->find($data['coursId']);
        if (!$ec) {
            $this->logger->warning('Cours non trouvé', ['ec_id' => $data['coursId']]);
            return $this->json(['message' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
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
            return $this->json(['message' => 'Validation échouée', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();

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
     * @Route("/commentaires/{id}", name="api_student_update_commentaire", methods={"PUT"})
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
            return $this->json(['message' => 'Validation échouée', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

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
 * @Route("/commentaires/{id}", name="api_student_delete_commentaire", methods={"DELETE"})
 */
public function deleteCommentaire(int $id, CommentaireRepository $commentaireRepository): JsonResponse
{
    $user = $this->security->getUser();
    if (!$user) {
        $this->logger->error('Utilisateur non authentifié', ['user' => $user ? $user->getEmail() : null]);
        return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
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

    $this->deleteChildren($commentaire, $commentaireRepository);

    $this->entityManager->remove($commentaire);
    $this->entityManager->flush();

    $this->logger->info('Commentaire supprimé avec succès', ['commentaire_id' => $id, 'user_id' => $user->getId()]);

    return $this->json(['message' => 'Commentaire supprimé avec succès']);
}

private function deleteChildren(Commentaire $commentaire, CommentaireRepository $commentaireRepository): void
{
    $children = $commentaire->getChildren();
    foreach ($children as $child) {
        $this->deleteChildren($child, $commentaireRepository);
        $this->entityManager->remove($child);
    }
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