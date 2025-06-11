<?php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Entity\User;
use App\Entity\Province;
use App\Entity\Years;
use App\Repository\EtudiantRepository;
use App\Repository\ProfRepository;
use App\Repository\UserRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Repository\NiveauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/api")
 */
class UserManagementController extends AbstractController
{
    private $etudiantRepository;
    private $profRepository;
    private $userRepository;
    private $entityManager;
    private $security;
    private $logger;
    private $serializer;
    private $mentionRepository;
    private $parcoursRepository;
    private $niveauRepository;
    private $passwordHasher;

    public function __construct(
        EtudiantRepository $etudiantRepository,
        ProfRepository $profRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        MentionRepository $mentionRepository,
        ParcoursRepository $parcoursRepository,
        NiveauRepository $niveauRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->etudiantRepository = $etudiantRepository;
        $this->profRepository = $profRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->mentionRepository = $mentionRepository;
        $this->parcoursRepository = $parcoursRepository;
        $this->niveauRepository = $niveauRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/users", name="api_users", methods={"GET"})
     */
    public function getUsers(): Response
    {
        $baseUrl = $this->getParameter('app.base_url');
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $etudiants = $this->etudiantRepository->findAll();
        $profs = $this->profRepository->findAll();
        $admins = $this->userRepository->findByRole('ROLE_ADMIN', $user->getId());

        $items = [];

        foreach ($etudiants as $etudiant) {
            $user = $etudiant->getUser();
            $nameParts = $user->getName() ? explode(' ', $user->getName()) : ['N/A', ''];
            $items[] = [
                '@id' => '/api/etudiants/' . $etudiant->getId(),
                'id' => $user->getId(),
                'etudiantId' => $etudiant->getId(),
                'type' => 'etudiant',
                'nom' => $nameParts[0],
                'prenom' => $nameParts[1] ?? '',
                'email' => $user->getEmail() ?? 'N/A',
                'telephone' => $user->getTelephone() ?? 'N/A',
                'avatar' => $user->getAvatar() ? $baseUrl . '/uploads/avatars/' . $user->getAvatar() : null,
                'matricule' => $etudiant->getMatricule() ?? 'N/A',
                'mention' => $etudiant->getMention() ? [
                    'id' => $etudiant->getMention()->getId(),
                    'name' => $etudiant->getMention()->getName()
                ] : null,
                'parcours' => $etudiant->getParcours() ? [
                    'id' => $etudiant->getParcours()->getId(),
                    'name' => $etudiant->getParcours()->getName()
                ] : null,
                'niveau' => $etudiant->getNiveau() ? [
                    'id' => $etudiant->getNiveau()->getId(),
                    'name' => $etudiant->getNiveau()->getNom()
                ] : null,
                'status' => $user->isStatus(),
                'province' => $user->getProvince() ? [
                    'id' => $user->getProvince()->getId(),
                    'region' => $user->getProvince()->getRegion()
                ] : null,
                'ville' => $user->getVille() ?? 'N/A',
                'type_payement' => $etudiant->getTypePayement() ?? 'N/A',
                'reference' => $etudiant->getReference() ?? 'N/A',
                'year' => $etudiant->getYear() ? [
                    'id' => $etudiant->getYear()->getId(),
                    'year' => $etudiant->getYear()->getYear()
                ] : null,
            ];
        }

        foreach ($profs as $prof) {
            $user = $prof->getUser();
            $nameParts = $user->getName() ? explode(' ', $user->getName()) : ['N/A', ''];
            $items[] = [
                '@id' => '/api/profs/' . $prof->getId(),
                'id' => $user->getId(),
                'profId' => $prof->getId(),
                'type' => 'prof',
                'nom' => $nameParts[0],
                'prenom' => $nameParts[1] ?? '',
                'email' => $user->getEmail() ?? 'N/A',
                'telephone' => $user->getTelephone() ?? 'N/A',
                'avatar' => $user->getAvatar() ? $baseUrl . '/uploads/avatars/' . $user->getAvatar() : null,
                'status' => $user->isStatus(),
                'province' => $user->getProvince() ? [
                    'id' => $user->getProvince()->getId(),
                    'region' => $user->getProvince()->getRegion()
                ] : null,
                'ville' => $user->getVille() ?? 'N/A',
            ];
        }

        foreach ($admins as $admin) {
        $nameParts = $admin->getName() ? explode(' ', $admin->getName()) : ['N/A', ''];
        $items[] = [
            '@id' => '/api/users/' . $admin->getId(),
            'id' => $admin->getId(),
            'type' => 'admin',
            'nom' => $nameParts[0],
            'prenom' => $nameParts[1] ?? '',
            'email' => $admin->getEmail() ?? 'N/A',
            'telephone' => $admin->getTelephone() ?? 'N/A',
            'avatar' => $admin->getAvatar() ? $baseUrl . '/uploads/avatars/' . $admin->getAvatar() : null,
            'status' => $admin->isStatus(),
            'province' => $admin->getProvince() ? [
                'id' => $admin->getProvince()->getId(),
                'region' => $admin->getProvince()->getRegion()
            ] : null,
            'ville' => $admin->getVille() ?? 'N/A',
        ];
    }

    return $this->json([
        'hydra:member' => $items,
        'hydra:totalItems' => count($items),
    ], Response::HTTP_OK, [], ['groups' => ['user:list']]);
}

    /**
     * @Route("/etudiants", name="api_create_etudiant", methods={"POST"})
     */
    public function createEtudiant(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->all();
        $file = $request->files->get('avatarFile');

        if (!isset($data['email'], $data['nom'], $data['prenom'], $data['password'], $data['matricule'], $data['mention'], $data['parcours'], $data['niveau'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $mention = $this->mentionRepository->find($data['mention']);
        $parcours = $this->parcoursRepository->find($data['parcours']);
        $niveau = $this->niveauRepository->find($data['niveau']);

        if (!$mention || !$parcours || !$niveau) {
            return $this->json(['message' => 'Mention, parcours ou niveau non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $newUser = new User();
        $newUser->setEmail($data['email']);
        $newUser->setName($data['nom'] . ' ' . $data['prenom']);
        $newUser->setTelephone($data['telephone'] ?? null);
        
        $province = null;
        if (!empty($data['province'])) {
            $province = $this->entityManager->getRepository(Province::class)->find($data['province']);
            if (!$province) {
                return $this->json(['message' => 'Province non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }
        $newUser->setProvince($province);
        $newUser->setVille($data['ville'] ?? null);
        $newUser->setRoles(['ROLE_ETUDIANT']);
        $newUser->setStatus(filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN));
        $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $data['password']));

        if ($file) {
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avatars', $fileName);
            $newUser->setAvatar($fileName);
        }

        $etudiant = new Etudiant();
        $etudiant->setUser($newUser);
        $etudiant->setMatricule($data['matricule']);
        $etudiant->setMention($mention);
        $etudiant->setParcours($parcours);
        $etudiant->setNiveau($niveau);
        $etudiant->setTypePayement($data['type_payement'] ?? null);
        $etudiant->setReference($data['reference'] ?? null);
        $etudiant->setDateInscription(new \DateTimeImmutable());
        
        $year = null;
        if (isset($data['year'])) {
            $year = $this->entityManager->getRepository(Years::class)->find($data['year']);
            if (!$year && $data['year'] !== null) {
                return $this->json(['message' => 'Année non trouvée'], Response::HTTP_BAD_REQUEST);
            }
            $etudiant->setYear($year);
        }

        try {
            $this->entityManager->persist($newUser);
            $this->entityManager->persist($etudiant);
            $this->entityManager->flush();

            $responseData = [
                'id' => $newUser->getId(),
                'etudiantId' => $etudiant->getId(),
                'type' => 'etudiant',
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'] ?? 'N/A',
                'avatar' => $newUser->getAvatar() ? $this->getParameter('app.base_url') . '/uploads/avatars/' . $newUser->getAvatar() : null,
                'matricule' => $data['matricule'],
                'mention' => ['id' => $mention->getId(), 'name' => $mention->getName()],
                'parcours' => ['id' => $parcours->getId(), 'name' => $parcours->getName()],
                'niveau' => ['id' => $niveau->getId(), 'name' => $niveau->getNom()],
                'status' => $newUser->isStatus(),
                'province' => $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null,
                'ville' => $data['ville'] ?? 'N/A',
                'type_payement' => $data['type_payement'] ?? 'N/A',
                'reference' => $data['reference'] ?? 'N/A',
                'year' => $year ? ['id' => $year->getId(), 'year' => $year->getYear()] : null,
            ];

            return $this->json($responseData, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'étudiant', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/profs", name="api_create_prof", methods={"POST"})
     */
    public function createProf(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->all();
        $file = $request->files->get('avatarFile');

        if (!isset($data['email'], $data['nom'], $data['prenom'], $data['password'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $newUser = new User();
        $newUser->setEmail($data['email']);
        $newUser->setName($data['nom'] . ' ' . $data['prenom']);
        $newUser->setTelephone($data['telephone'] ?? null);
        
        $province = null;
        if (!empty($data['province'])) {
            $province = $this->entityManager->getRepository(Province::class)->find($data['province']);
            if (!$province) {
                return $this->json(['message' => 'Province non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }
        $newUser->setProvince($province);
        $newUser->setVille($data['ville'] ?? null);
        $newUser->setRoles(['ROLE_PROFFESSEUR']);
        $newUser->setStatus(filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN));
        $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $data['password']));

        if ($file) {
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avatars', $fileName);
            $newUser->setAvatar($fileName);
        }

        $prof = new Prof();
        $prof->setUser($newUser);

        try {
            $this->entityManager->persist($newUser);
            $this->entityManager->persist($prof);
            $this->entityManager->flush();

            $responseData = [
                'id' => $newUser->getId(),
                'profId' => $prof->getId(),
                'type' => 'prof',
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'] ?? 'N/A',
                'avatar' => $newUser->getAvatar() ? $this->getParameter('app.base_url') . '/uploads/avatars/' . $newUser->getAvatar() : null,
                'status' => $newUser->isStatus(),
                'province' => $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null,
                'ville' => $data['ville'] ?? 'N/A',
            ];

            return $this->json($responseData, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'enseignant', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/admins", name="api_create_admin", methods={"POST"})
     */
    public function createAdmin(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->all();
        $file = $request->files->get('avatarFile');

        if (!isset($data['email'], $data['nom'], $data['prenom'], $data['password'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $newUser = new User();
        $newUser->setEmail($data['email']);
        $newUser->setName($data['nom'] . ' ' . $data['prenom']);
        $newUser->setTelephone($data['telephone'] ?? null);
        
        $province = null;
        if (!empty($data['province'])) {
            $province = $this->entityManager->getRepository(Province::class)->find($data['province']);
            if (!$province) {
                return $this->json(['message' => 'Province non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }
        $newUser->setProvince($province);
        $newUser->setVille($data['ville'] ?? null);
        $newUser->setRoles(['ROLE_ADMIN']);
        $newUser->setStatus(filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN));
        $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $data['password']));

        if ($file) {
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avatars', $fileName);
            $newUser->setAvatar($fileName);
        }

        try {
            $this->entityManager->persist($newUser);
            $this->entityManager->flush();

            $responseData = [
                'id' => $newUser->getId(),
                'type' => 'admin',
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'] ?? 'N/A',
                'avatar' => $newUser->getAvatar() ? $this->getParameter('app.base_url') . '/uploads/avatars/' . $newUser->getAvatar() : null,
                'status' => $newUser->isStatus(),
                'province' => $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null,
                'ville' => $data['ville'] ?? 'N/A',
            ];

            return $this->json($responseData, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'administrateur', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
 * @Route("/etudiants/{id}", name="api_update_etudiant", methods={"PATCH"})
 */
public function updateEtudiant(int $id, Request $request): Response
{
    $user = $this->security->getUser();
    if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
    }

    $etudiant = $this->etudiantRepository->find($id);
    if (!$etudiant) {
        return $this->json(['message' => 'Étudiant non trouvé'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
    }

    $userEntity = $etudiant->getUser();
    $updatedFields = [];

    if (isset($data['email'])) {
        $userEntity->setEmail($data['email']);
        $updatedFields['email'] = $data['email'];
    }
    if (isset($data['nom'], $data['prenom'])) {
        $userEntity->setName($data['nom'] . ' ' . $data['prenom']);
        $updatedFields['nom'] = $data['nom'];
        $updatedFields['prenom'] = $data['prenom'];
    }
    if (isset($data['telephone'])) {
        $userEntity->setTelephone($data['telephone']);
        $updatedFields['telephone'] = $data['telephone'] ?? 'N/A';
    }
    if (isset($data['province'])) {
        $province = $data['province'] ? $this->entityManager->getRepository(Province::class)->find($data['province']) : null;
        $userEntity->setProvince($province);
        $updatedFields['province'] = $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null;
    }
    if (isset($data['ville'])) {
        $userEntity->setVille($data['ville']);
        $updatedFields['ville'] = $data['ville'] ?? 'N/A';
    }
    if (isset($data['status'])) {
        $userEntity->setStatus(filter_var($data['status'], FILTER_VALIDATE_BOOLEAN));
        $updatedFields['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
    }
    if (isset($data['password'])) {
        $userEntity->setPassword($this->passwordHasher->hashPassword($userEntity, $data['password']));
    }
    if (isset($data['matricule'])) {
        $etudiant->setMatricule($data['matricule']);
        $updatedFields['matricule'] = $data['matricule'];
    }
    if (isset($data['mention'])) {
        $mention = $this->mentionRepository->find($data['mention']);
        if (!$mention) {
            return $this->json(['message' => 'Mention non trouvée'], Response::HTTP_BAD_REQUEST);
        }
        $etudiant->setMention($mention);
        $updatedFields['mention'] = ['id' => $mention->getId(), 'name' => $mention->getName()];
    }
    if (isset($data['parcours'])) {
        $parcours = $this->parcoursRepository->find($data['parcours']);
        if (!$parcours) {
            return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_BAD_REQUEST);
        }
        $etudiant->setParcours($parcours);
        $updatedFields['parcours'] = ['id' => $parcours->getId(), 'name' => $parcours->getName()];
    }
    if (isset($data['niveau'])) {
        $niveau = $this->niveauRepository->find($data['niveau']);
        if (!$niveau) {
            return $this->json(['message' => 'Niveau non trouvé'], Response::HTTP_BAD_REQUEST);
        }
        $etudiant->setNiveau($niveau);
        $updatedFields['niveau'] = ['id' => $niveau->getId(), 'name' => $niveau->getNom()];
    }
    if (isset($data['type_payement'])) {
        $etudiant->setTypePayement($data['type_payement']);
        $updatedFields['type_payement'] = $data['type_payement'] ?? 'N/A';
    }
    if (isset($data['reference'])) {
        $etudiant->setReference($data['reference']);
        $updatedFields['reference'] = $data['reference'] ?? 'N/A';
    }
    if (isset($data['year'])) {
        $year = $data['year'] ? $this->entityManager->getRepository(Years::class)->find($data['year']) : null;
        $etudiant->setYear($year);
        $updatedFields['year'] = $year ? ['id' => $year->getId(), 'year' => $year->getYear()] : null;
    }

    try {
        $this->entityManager->persist($etudiant);
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();

        $this->logger->info('Étudiant mis à jour avec succès', [
            'id' => $etudiant->getId(),
            'updatedFields' => $updatedFields
        ]);

        return $this->json([
            'message' => 'Étudiant mis à jour avec succès',
            '@id' => '/api/etudiants/' . $etudiant->getId(),
            'id' => $userEntity->getId(), // User ID
            'etudiantId' => $etudiant->getId(),
            'updatedFields' => $updatedFields
        ], Response::HTTP_OK);
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la mise à jour de l\'étudiant', [
            'id' => $id,
            'error' => $e->getMessage(),
            'data' => $data
        ]);
        return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    /**
     * @Route("/profs/{id}", name="api_update_prof", methods={"PATCH"})
     */
    public function updateProf(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $prof = $this->profRepository->find($id);
        if (!$prof) {
            return $this->json(['message' => 'Enseignant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        $userEntity = $prof->getUser();
        $updatedFields = [];

        if (isset($data['email'])) {
            $userEntity->setEmail($data['email']);
            $updatedFields['email'] = $data['email'];
        }
        if (isset($data['nom'], $data['prenom'])) {
            $userEntity->setName($data['nom'] . ' ' . $data['prenom']);
            $updatedFields['nom'] = $data['nom'];
            $updatedFields['prenom'] = $data['prenom'];
        }
        if (isset($data['telephone'])) {
            $userEntity->setTelephone($data['telephone']);
            $updatedFields['telephone'] = $data['telephone'] ?? 'N/A';
        }
        if (isset($data['province'])) {
            $province = $data['province'] ? $this->entityManager->getRepository(Province::class)->find($data['province']) : null;
            $userEntity->setProvince($province);
            $updatedFields['province'] = $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null;
        }
        if (isset($data['ville'])) {
            $userEntity->setVille($data['ville']);
            $updatedFields['ville'] = $data['ville'] ?? 'N/A';
        }
        if (isset($data['status'])) {
            $userEntity->setStatus(filter_var($data['status'], FILTER_VALIDATE_BOOLEAN));
            $updatedFields['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['password'])) {
            $userEntity->setPassword($this->passwordHasher->hashPassword($userEntity, $data['password']));
        }

        try {
            $this->entityManager->persist($prof);
            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Enseignant mis à jour avec succès',
                '@id' => '/api/profs/' . $prof->getId(),
                'id' => $userEntity->getId(),
                'profId' => $prof->getId(),
                'updatedFields' => $updatedFields
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'enseignant', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/admins/{id}", name="api_update_admin", methods={"PATCH"})
     */
    public function updateAdmin(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $admin = $this->userRepository->find($id);
        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
            return $this->json(['message' => 'Administrateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Erreur de format JSON'], Response::HTTP_BAD_REQUEST);
        }

        $updatedFields = [];

        if (isset($data['email'])) {
            $admin->setEmail($data['email']);
            $updatedFields['email'] = $data['email'];
        }
        if (isset($data['nom'], $data['prenom'])) {
            $admin->setName($data['nom'] . ' ' . $data['prenom']);
            $updatedFields['nom'] = $data['nom'];
            $updatedFields['prenom'] = $data['prenom'];
        }
        if (isset($data['telephone'])) {
            $admin->setTelephone($data['telephone']);
            $updatedFields['telephone'] = $data['telephone'] ?? 'N/A';
        }
        if (isset($data['province'])) {
            $province = $data['province'] ? $this->entityManager->getRepository(Province::class)->find($data['province']) : null;
            $admin->setProvince($province);
            $updatedFields['province'] = $province ? ['id' => $province->getId(), 'region' => $province->getRegion()] : null;
        }
        if (isset($data['ville'])) {
            $admin->setVille($data['ville']);
            $updatedFields['ville'] = $data['ville'] ?? 'N/A';
        }
        if (isset($data['status'])) {
            $admin->setStatus(filter_var($data['status'], FILTER_VALIDATE_BOOLEAN));
            $updatedFields['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['password'])) {
            $admin->setPassword($this->passwordHasher->hashPassword($admin, $data['password']));
        }

        try {
            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Administrateur mis à jour avec succès',
                '@id' => '/api/users/' . $admin->getId(),
                'id' => $admin->getId(),
                'updatedFields' => $updatedFields
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'administrateur', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/users/{id}/upload", name="api_upload_user_avatar", methods={"POST"})
     */
    public function uploadUserAvatar(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $userEntity = $this->userRepository->find($id);
        if (!$userEntity) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('avatarFile');
        if (!$file) {
            return $this->json(['message' => 'Aucun fichier fourni'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $validMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file->getMimeType(), $validMimeTypes)) {
                return $this->json(['message' => 'Type de fichier non supporté'], Response::HTTP_BAD_REQUEST);
            }

            if ($userEntity->getAvatar()) {
                $oldFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $userEntity->getAvatar();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avatars', $fileName);
            $userEntity->setAvatar($fileName);

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Avatar modifié avec succès',
                'avatar' => $this->getParameter('app.base_url') . '/uploads/avatars/' . $userEntity->getAvatar(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'upload de l\'avatar', ['error' => $e->getMessage()]);
            return $this->json(['message' => 'Erreur lors de l\'upload : ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/etudiants/{id}", name="api_delete_etudiant", methods={"DELETE"})
     */
    public function deleteEtudiant(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $etudiant = $this->etudiantRepository->find($id);
        if (!$etudiant) {
            return $this->json(['message' => 'Étudiant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $userEntity = $etudiant->getUser();
        if ($userEntity->getAvatar()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $userEntity->getAvatar();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->entityManager->remove($etudiant);
        $this->entityManager->remove($userEntity);
        $this->entityManager->flush();

        return $this->json(['message' => 'Étudiant supprimé avec succès'], Response::HTTP_OK);
    }

    /**
     * @Route("/profs/{id}", name="api_delete_prof", methods={"DELETE"})
     */
    public function deleteProf(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $prof = $this->profRepository->find($id);
        if (!$prof) {
            return $this->json(['message' => 'Enseignant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $userEntity = $prof->getUser();
        if ($userEntity->getAvatar()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $userEntity->getAvatar();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->entityManager->remove($prof);
        $this->entityManager->remove($userEntity);
        $this->entityManager->flush();

        return $this->json(['message' => 'Enseignant supprimé avec succès'], Response::HTTP_OK);
    }

    /**
     * @Route("/admins/{id}", name="api_delete_admin", methods={"DELETE"})
     */
    public function deleteAdmin(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $admin = $this->userRepository->find($id);
        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
            return $this->json(['message' => 'Administrateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($user->getId() === $admin->getId()) {
            return $this->json(['message' => 'Impossible de supprimer son propre compte'], Response::HTTP_FORBIDDEN);
        }

        if ($admin->getAvatar()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $admin->getAvatar();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->entityManager->remove($admin);
        $this->entityManager->flush();

        return $this->json(['message' => 'Administrateur supprimé avec succès'], Response::HTTP_OK);
    }

    /**
 * @Route("/admin/parcours", name="api_get_parcours", methods={"GET"})
 */
public function getParcours(Request $request, ParcoursRepository $parcoursRepository): Response
{
    $mentionId = $request->query->get('mention');
    $niveauId = $request->query->get('niveau');

    $criteria = [];
    if ($mentionId) {
        $criteria['mention'] = $mentionId;
    }
    if ($niveauId) {
        $criteria['niveau'] = $niveauId;
    }

    $parcours = $parcoursRepository->findByMentionAndNiveau($mentionId, $niveauId);
    $items = array_map(function ($parcour) {
        return [
            '@id' => '/api/parcours/' . $parcour->getId(),
            'id' => $parcour->getId(),
            'name' => $parcour->getName(),
            'full_name' => $parcour->getFullName(),
            'mention' => $parcour->getMention() ? [
                'id' => $parcour->getMention()->getId(),
                'name' => $parcour->getMention()->getName()
            ] : null,
            'niveau' => $parcour->getNiveau() ? [
                'id' => $parcour->getNiveau()->getId(),
                'nom' => $parcour->getNiveau()->getNom()
            ] : null,
        ];
    }, $parcours);

    $this->logger->info('Parcours récupérés', [
        'mentionId' => $mentionId,
        'niveauId' => $niveauId,
        'count' => count($items)
    ]);

    return $this->json([
        'hydra:member' => $items,
        'hydra:totalItems' => count($items),
    ], Response::HTTP_OK);
}
}