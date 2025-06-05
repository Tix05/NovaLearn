<?php

namespace App\Controller\Api;

use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Entity\UeParcours;
use App\Entity\Ec;
use App\Entity\Years;
use App\Repository\MentionRepository;
use App\Repository\NiveauRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use App\Repository\UeParcoursRepository;
use App\Repository\EcRepository;
use App\Repository\YearsRepository;
use App\Repository\ProfRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @Route("/api")
 */
class ParametreController extends AbstractController
{
    private $entityManager;
    private $security;
    private $logger;
    private $serializer;
    private $storage;
    private $uploadHandler;
    private $mentionRepository;
    private $niveauRepository;
    private $parcoursRepository;
    private $semestreRepository;
    private $ueRepository;
    private $ueParcoursRepository;
    private $ecRepository;
    private $yearsRepository;
    private $profRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        StorageInterface $storage,
        UploadHandler $uploadHandler,
        MentionRepository $mentionRepository,
        NiveauRepository $niveauRepository,
        ParcoursRepository $parcoursRepository,
        SemestreRepository $semestreRepository,
        UeRepository $ueRepository,
        UeParcoursRepository $ueParcoursRepository,
        EcRepository $ecRepository,
        YearsRepository $yearsRepository,
        ProfRepository $profRepository
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->storage = $storage;
        $this->uploadHandler = $uploadHandler;
        $this->mentionRepository = $mentionRepository;
        $this->niveauRepository = $niveauRepository;
        $this->parcoursRepository = $parcoursRepository;
        $this->semestreRepository = $semestreRepository;
        $this->ueRepository = $ueRepository;
        $this->ueParcoursRepository = $ueParcoursRepository;
        $this->ecRepository = $ecRepository;
        $this->yearsRepository = $yearsRepository;
        $this->profRepository = $profRepository;
    }

    // Helper function to check admin role
    private function checkAdminRole(): ?Response
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

        return null;
    }

    // Helper function to handle file uploads for Mention
    private function handleFileUpload($iconFile, Mention $mention): ?Response
    {
        if (!$iconFile) {
            return $this->json(['message' => 'Fichier icône requis'], Response::HTTP_BAD_REQUEST);
        }

        $validMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($iconFile->getMimeType(), $validMimeTypes)) {
            $this->logger->warning('Type de fichier non supporté', ['mime_type' => $iconFile->getMimeType()]);
            return $this->json(['message' => 'Type de fichier non supporté. Formats autorisés : JPEG, PNG, GIF'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $fileName = uniqid() . '.' . $iconFile->getClientOriginalExtension();
            $iconFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/icons', $fileName);
            $mention->setIcon($fileName);
            $mention->setIconFile($iconFile);
            $mention->setIconUpdatedAt(new \DateTimeImmutable());
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'upload du fichier', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de l\'upload : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return null;
    }

    /**
     * @Route("/mentions", name="api_mentions_create", methods={"POST"})
     */
    public function createMention(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $name = $request->request->get('name');
        $iconFile = $request->files->get('iconFile');

        if (!$name || !$iconFile) {
            $this->logger->warning('Données manquantes', [
                'name' => $name ? 'present' : 'missing',
                'iconFile' => $iconFile ? 'present' : 'missing',
            ]);
            return $this->json(['message' => 'Nom et fichier icône requis'], Response::HTTP_BAD_REQUEST);
        }

        $mention = new Mention();
        $mention->setName($name);

        if ($errorResponse = $this->handleFileUpload($iconFile, $mention)) {
            return $errorResponse;
        }

        try {
            $this->entityManager->persist($mention);
            $this->entityManager->flush();

            $this->logger->info('Mention créée avec succès', ['id' => $mention->getId(), 'name' => $name]);

            $data = $this->serializer->serialize($mention, 'json', ['groups' => ['mention:read']]);
            return new Response($data, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la mention', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mentions/{id}", name="api_mentions_update", methods={"PATCH"})
     */
    public function updateMention(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $mention = $this->mentionRepository->find($id);
        if (!$mention) {
            $this->logger->warning('Mention non trouvée', ['id' => $id]);
            return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Erreur de décodage JSON', ['error' => json_last_error_msg()]);
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name']) && is_string($data['name'])) {
            $mention->setName($data['name']);
        }

        try {
            $this->entityManager->persist($mention);
            $this->entityManager->flush();

            $this->logger->info('Mention mise à jour avec succès', ['id' => $id]);
            return $this->json($mention, Response::HTTP_OK, [], ['groups' => ['mention:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de la mention', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mentions/{id}/upload", name="api_mentions_upload_icon", methods={"POST"})
     */
    public function uploadMentionIcon(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $mention = $this->mentionRepository->find($id);
        if (!$mention) {
            $this->logger->warning('Mention non trouvée', ['id' => $id]);
            return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $iconFile = $request->files->get('iconFile');
        if (!$iconFile) {
            $this->logger->warning('Aucun fichier fourni');
            return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
        }

        if ($errorResponse = $this->handleFileUpload($iconFile, $mention)) {
            return $errorResponse;
        }

        try {
            $this->entityManager->persist($mention);
            $this->entityManager->flush();

            $this->logger->info('Icône mise à jour avec succès', ['id' => $id]);
            return $this->json([
                'message' => 'Icône modifiée avec succès',
                'icon' => $this->getParameter('app.base_url') . '/uploads/icons/' . $mention->getIcon(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'upload de l\'icône', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de l\'upload : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mentions/{id}", name="api_mentions_delete", methods={"DELETE"})
     */
    public function deleteMention(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $mention = $this->mentionRepository->find($id);
        if (!$mention) {
            $this->logger->warning('Mention non trouvée', ['id' => $id]);
            return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Parcours')->findBy(['mention' => $id]);
        if (count($dependents) > 0) {
            $this->logger->warning('Suppression interdite : mention liée à des parcours', ['id' => $id]);
            return $this->json(['message' => 'Suppression impossible : mention utilisée par des parcours'], Response::HTTP_CONFLICT);
        }

        if ($mention->getIcon()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/icons/' . $mention->getIcon();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        try {
            $this->entityManager->remove($mention);
            $this->entityManager->flush();

            $this->logger->info('Mention supprimée avec succès', ['id' => $id]);
            return $this->json(['message' => 'Mention supprimée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de la mention', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/niveaux", name="api_niveaux_create", methods={"POST"})
     */
    public function createNiveau(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['code'], $data['nom'], $data['cycle'], $data['ordre'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $niveau = new Niveau();
        $niveau->setCode($data['code']);
        $niveau->setNom($data['nom']);
        $niveau->setCycle($data['cycle']);
        $niveau->setOrdre((int)$data['ordre']);

        try {
            $this->entityManager->persist($niveau);
            $this->entityManager->flush();

            return $this->json($niveau, Response::HTTP_CREATED, [], ['groups' => ['niveau:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du niveau', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/niveaux/{id}", name="api_niveaux_update", methods={"PATCH"})
     */
    public function updateNiveau(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $niveau = $this->niveauRepository->find($id);
        if (!$niveau) {
            return $this->json(['message' => 'Niveau non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['code'])) $niveau->setCode($data['code']);
        if (isset($data['nom'])) $niveau->setNom($data['nom']);
        if (isset($data['cycle'])) $niveau->setCycle($data['cycle']);
        if (isset($data['ordre'])) $niveau->setOrdre((int)$data['ordre']);

        try {
            $this->entityManager->persist($niveau);
            $this->entityManager->flush();

            return $this->json($niveau, Response::HTTP_OK, [], ['groups' => ['niveau:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du niveau', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/niveaux/{id}", name="api_niveaux_delete", methods={"DELETE"})
     */
    public function deleteNiveau(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $niveau = $this->niveauRepository->find($id);
        if (!$niveau) {
            return $this->json(['message' => 'Niveau non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Parcours')->findBy(['niveau' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : niveau utilisé par des parcours'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($niveau);
            $this->entityManager->flush();

            return $this->json(['message' => 'Niveau supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression du niveau', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/parcours", name="api_parcours_create", methods={"POST"})
     */
    public function createParcours(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['name'], $data['full_name'], $data['niveau'], $data['mention'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $niveau = $this->niveauRepository->find($data['niveau']);
        $mention = $this->mentionRepository->find($data['mention']);
        if (!$niveau || !$mention) {
            return $this->json(['message' => 'Niveau ou mention invalide'], Response::HTTP_BAD_REQUEST);
        }

        $parcours = new Parcours();
        $parcours->setName($data['name']);
        $parcours->setFullName($data['full_name']);
        $parcours->setNiveau($niveau);
        $parcours->setMention($mention);

        try {
            $this->entityManager->persist($parcours);
            $this->entityManager->flush();

            return $this->json($parcours, Response::HTTP_CREATED, [], ['groups' => ['parcours:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du parcours', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/parcours/{id}", name="api_parcours_update", methods={"PATCH"})
     */
    public function updateParcours(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $parcours = $this->parcoursRepository->find($id);
        if (!$parcours) {
            return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) $parcours->setName($data['name']);
        if (isset($data['full_name'])) $parcours->setFullName($data['full_name']);
        if (isset($data['niveau'])) {
            $niveau = $this->niveauRepository->find($data['niveau']);
            if (!$niveau) return $this->json(['message' => 'Niveau invalide'], Response::HTTP_BAD_REQUEST);
            $parcours->setNiveau($niveau);
        }
        if (isset($data['mention'])) {
            $mention = $this->mentionRepository->find($data['mention']);
            if (!$mention) return $this->json(['message' => 'Mention invalide'], Response::HTTP_BAD_REQUEST);
            $parcours->setMention($mention);
        }

        try {
            $this->entityManager->persist($parcours);
            $this->entityManager->flush();

            return $this->json($parcours, Response::HTTP_OK, [], ['groups' => ['parcours:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du parcours', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/parcours/{id}", name="api_parcours_delete", methods={"DELETE"})
     */
    public function deleteParcours(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $parcours = $this->parcoursRepository->find($id);
        if (!$parcours) {
            return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Etudiant')->findBy(['parcours' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : parcours utilisé par des étudiants'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($parcours);
            $this->entityManager->flush();

            return $this->json(['message' => 'Parcours supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression du parcours', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/semestres", name="api_semestres_create", methods={"POST"})
     */
    public function createSemestre(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['name'], $data['code'], $data['niveau'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $niveau = $this->niveauRepository->find($data['niveau']);
        if (!$niveau) {
            return $this->json(['message' => 'Niveau invalide'], Response::HTTP_BAD_REQUEST);
        }

        $semestre = new Semestre();
        $semestre->setName($data['name']);
        $semestre->setCode($data['code']);
        $semestre->setNiveau($niveau);

        try {
            $this->entityManager->persist($semestre);
            $this->entityManager->flush();

            return $this->json($semestre, Response::HTTP_CREATED, [], ['groups' => ['semestre:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du semestre', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/semestres/{id}", name="api_semestres_update", methods={"PATCH"})
     */
    public function updateSemestre(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $semestre = $this->semestreRepository->find($id);
        if (!$semestre) {
            return $this->json(['message' => 'Semestre non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) $semestre->setName($data['name']);
        if (isset($data['code'])) $semestre->setCode($data['code']);
        if (isset($data['niveau'])) {
            $niveau = $this->niveauRepository->find($data['niveau']);
            if (!$niveau) return $this->json(['message' => 'Niveau invalide'], Response::HTTP_BAD_REQUEST);
            $semestre->setNiveau($niveau);
        }

        try {
            $this->entityManager->persist($semestre);
            $this->entityManager->flush();

            return $this->json($semestre, Response::HTTP_OK, [], ['groups' => ['semestre:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du semestre', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/semestres/{id}", name="api_semestres_delete", methods={"DELETE"})
     */
    public function deleteSemestre(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $semestre = $this->semestreRepository->find($id);
        if (!$semestre) {
            return $this->json(['message' => 'Semestre non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Ue')->findBy(['semestre' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : semestre utilisé par des UEs'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($semestre);
            $this->entityManager->flush();

            return $this->json(['message' => 'Semestre supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression du semestre', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ues", name="api_ues_create", methods={"POST"})
     */
    public function createUe(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['name'], $data['mention'], $data['semestre'], $data['parcours'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $mention = $this->mentionRepository->find($data['mention']);
        $semestre = $this->semestreRepository->find($data['semestre']);
        if (!$mention || !$semestre) {
            return $this->json(['message' => 'Mention ou semestre invalide'], Response::HTTP_BAD_REQUEST);
        }

        $ue = new Ue();
        $ue->setName($data['name']);
        $ue->setCode($data['code'] ?? null);
        $ue->setMention($mention);
        $ue->setSemestre($semestre);

        // Handle parcours
        foreach ($data['parcours'] as $parcoursId) {
            $parcours = $this->parcoursRepository->find($parcoursId);
            if ($parcours) {
                $ueParcours = new UeParcours();
                $ueParcours->setUe($ue);
                $ueParcours->setParcours($parcours);
                $this->entityManager->persist($ueParcours);
            }
        }

        try {
            $this->entityManager->persist($ue);
            $this->entityManager->flush();

            return $this->json($ue, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error creating UE', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Error creating UE: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/ues/{id}", name="api_ues_update", methods={"PATCH"})
     */
    public function updateUe(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $ue = $this->ueRepository->find($id); if (!$ue) {
            return $this->json(['message' => 'UE not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON format error'], ['message' => Response::HTTP_BAD_REQUEST]);
        }

        if (isset($data['name'])) $ue->setName($data['name']);
        if (isset($data['code'])) $ue->setCode($data['code'] ?? null);
        if (isset($data['mention'])) {
            $mention = $this->mentionRepository->find($data['mention']);
            if (!$mention) return $this->json(['message' => 'Invalid mention'], Response::HTTP_BAD_REQUEST);
            $ue->setMention($mention);
        }
        if (isset($data['semestre'])) {
            $semestre = $this->semestreRepository->find($data['semestre']);
            if (!$semestre) return $this->json(['message' => 'Invalid semestre'], Response::HTTP_BAD_REQUEST);
            $ue->setSemestre($semestre);
        }

        // Handle parcours
        if (isset($data['parcours'])) {
            // Remove existing UeParcours
            $existingUeParcours = $this->ueParcoursRepository->findBy(['ue' => $id]);
            foreach ($existingUeParcours as $ueParcours) {
                $this->entityManager->remove($ueParcours);
            }

            // Add new UeParcours
            foreach ($data['parcours'] as $parcoursId) {
                $parcours = $this->parcoursRepository->find($parcoursId);
                if ($parcours) {
                    $ueParcours = new UeParcours();
                    $ueParcours->setUe($ue);
                    $ueParcours->setParcours($parcours);
                    $this->entityManager->persist($ueParcours);
                }
            }
        }

        try {
            $this->entityManager->persist($ue);
            $this->entityManager->flush();

            return $this->json($ue, Response::HTTP_OK, [], ['groups' => ['ue:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating UE', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Error updating UE: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/ues/{id}", name="api_ues_delete", methods={"DELETE"})
     */
    public function deleteUe(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $ue = $this->ueRepository->find($id);
        if (!$ue) {
            return $this->json(['message' => 'UE non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Ec')->findBy(['ue' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : UE utilisée par des ECs'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($ue);
            $this->entityManager->flush();

            return $this->json(['message' => 'UE supprimée'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de l\'UE', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ecs", name="api_ecs_create", methods={"POST"})
     */
    public function createEc(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['code'], $data['name'], $data['coeff'], $data['status'], $data['ue'], $data['prof'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ue = $this->ueRepository->find($data['ue']);
        $prof = $this->profRepository->find($data['prof']);
        if (!$ue || !$prof) {
            return $this->json(['message' => 'UE ou professeur invalide'], Response::HTTP_BAD_REQUEST);
        }

        $ec = new Ec();
        $ec->setCode($data['code']);
        $ec->setName($data['name']);
        $ec->setCoeff((int)$data['coeff']);
        $ec->setStatus($data['status']);
        $ec->setDescription($data['description'] ?? null);
        $ec->setUe($ue);
        $ec->setProf($prof);

        try {
            $this->entityManager->persist($ec);
            $this->entityManager->flush();

            return $this->json($ec, Response::HTTP_CREATED, [], ['groups' => ['ec:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'EC', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ecs/{id}", name="api_ecs_update", methods={"PATCH"})
     */
    public function updateEc(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $ec = $this->ecRepository->find($id);
        if (!$ec) {
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['code'])) $ec->setCode($data['code']);
        if (isset($data['name'])) $ec->setName($data['name']);
        if (isset($data['coeff'])) $ec->setCoeff((int)$data['coeff']);
        if (isset($data['status'])) $ec->setStatus($data['status']);
        if (isset($data['description'])) $ec->setDescription($data['description']);
        if (isset($data['ue'])) {
            $ue = $this->ueRepository->find($data['ue']);
            if (!$ue) return $this->json(['message' => 'UE invalide'], Response::HTTP_BAD_REQUEST);
            $ec->setUe($ue);
        }
        if (isset($data['prof'])) {
            $prof = $this->profRepository->find($data['prof']);
            if (!$prof) return $this->json(['message' => 'Professeur invalide'], Response::HTTP_BAD_REQUEST);
            $ec->setProf($prof);
        }

        try {
            $this->entityManager->persist($ec);
            $this->entityManager->flush();

            return $this->json($ec, Response::HTTP_OK, [], ['groups' => ['ec:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'EC', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ecs/{id}", name="api_ecs_delete", methods={"DELETE"})
     */
    public function deleteEc(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $ec = $this->ecRepository->find($id);
        if (!$ec) {
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Examen')->findBy(['ec' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : EC utilisé par des examens'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($ec);
            $this->entityManager->flush();

            return $this->json(['message' => 'EC supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de l\'EC', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/years", name="api_years_create", methods={"POST"})
     */
    public function createYear(Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['year'], $data['current'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $year = new Years();
        $year->setYear($data['year']);
        $year->setCurrent($data['current']);

        try {
            $this->entityManager->persist($year);
            $this->entityManager->flush();

            return $this->json($year, Response::HTTP_CREATED, [], ['groups' => ['years:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'année', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/years/{id}", name="api_years_update", methods={"PATCH"})
     */
    public function updateYear(int $id, Request $request): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $year = $this->yearsRepository->find($id);
        if (!$year) {
            return $this->json(['message' => 'Année non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['year'])) $year->setYear($data['year']);
        if (isset($data['current'])) $year->setCurrent($data['current']);

        try {
            $this->entityManager->persist($year);
            $this->entityManager->flush();

            return $this->json($year, Response::HTTP_OK, [], ['groups' => ['years:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'année', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/years/{id}", name="api_years_delete", methods={"DELETE"})
     */
    public function deleteYear(int $id): Response
    {
        if ($errorResponse = $this->checkAdminRole()) {
            return $errorResponse;
        }

        $year = $this->yearsRepository->find($id);
        if (!$year) {
            return $this->json(['message' => 'Année non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $dependents = $this->entityManager->getRepository('App\Entity\Etudiant')->findBy(['year' => $id]);
        if (count($dependents) > 0) {
            return $this->json(['message' => 'Suppression impossible : année utilisée par des étudiants'], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($year);
            $this->entityManager->flush();

            return $this->json(['message' => 'Année supprimée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de l\'année', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}