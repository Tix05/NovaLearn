<?php

namespace App\Controller\Api;

use App\Entity\Prof;
use App\Entity\FichierSupport;
use App\Entity\Ec;
use App\Entity\User;
use App\Repository\MentionRepository;
use App\Repository\EcRepository;
use App\Repository\FichierSupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface; // Importation correcte
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


/**
 * @Route("/api/teacher")
 */
class TeacherDataController extends AbstractController
{
    private $security;
    private $mentionRepository;
    private $entityManager;

    public function __construct(Security $security, MentionRepository $mentionRepository, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->mentionRepository = $mentionRepository;
        $this->entityManager = $entityManager;
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

            // Compression pour les vidéos
            if ($type === 'VIDEO') {
                try {
                    $compressedFileName = 'compressed_' . $fileName;
                    $compressedFilePath = $this->getParameter('supports_directory') . '/' . $compressedFileName;

                    $ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe';
                    $command = sprintf(
                        '%s -i %s -vcodec libx264 -b:v 1000k -acodec aac -b:a 128k %s',
                        escapeshellarg($ffmpegPath),
                        escapeshellarg($filePath),
                        escapeshellarg($compressedFilePath)
                    );
                    $logger->info('Exécution de la commande FFmpeg', ['command' => $command]);
                    exec($command, $output, $returnVar);

                    if ($returnVar !== 0) {
                        $logger->error('Erreur lors de la compression vidéo', ['output' => $output, 'return_var' => $returnVar]);
                        return $this->json(['message' => 'Erreur lors de la compression vidéo'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    $support->setFichier($compressedFileName);

                    // Supprimer le fichier original
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                } catch (\Exception $e) {
                    $logger->error('Erreur lors de la compression vidéo', ['error' => $e->getMessage(), 'file' => $filePath]);
                    return $this->json(['message' => 'Erreur lors de la compression de la vidéo : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $support->setFichier($fileName);
            }
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
}
