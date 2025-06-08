<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ConnectionLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * @Route("/api")
 */
class AuthController extends AbstractController
{
    private $passwordHasher;
    private $doctrine;
    private $JWTManager;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $JWTManager
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->doctrine = $doctrine;
        $this->JWTManager = $JWTManager;
    }

    /**
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        $user = $this->doctrine->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'message' => 'Email ou mot de passe incorrect'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isStatus()) {
            return $this->json([
                'message' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        $isEtudiant = false;
        foreach ($user->getEtudiants() as $etudiant) {
            if ($etudiant->isStatus()) {
                $isEtudiant = true;
                break;
            }
        }
        
        if (!$isEtudiant) {
            return $this->json([
                'message' => "Vous n'avez pas accès à l'espace étudiant"
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Mettre à jour le statut en ligne
        $user->setOnlineStatus('ONLINE');
        $user->setUpdatedAt(new \DateTimeImmutable());

        // Enregistrer la connexion
        $connectionLog = new ConnectionLog();
        $connectionLog->setUser($user);
        $connectionLog->setLoginTime(new \DateTimeImmutable());

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->persist($connectionLog);
        $entityManager->flush();
        
        $token = $this->JWTManager->create($user);
        
        return $this->json([
            'token' => $token,
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'avatar' => $user->getAvatar(),
            'roles' => $user->getRoles(),
            'onlineStatus' => $user->getOnlineStatus(),
            'etudiant' => $user->getEtudiants()->first() ? [
                'matricule' => $user->getEtudiants()->first()->getMatricule(),
                'mention' => $user->getEtudiants()->first()->getMention()->getName(),
                'parcours' => $user->getEtudiants()->first()->getParcours()->getName(),
                'niveau' => $user->getEtudiants()->first()->getNiveau()->getNom()
            ] : null
        ]);
    }

    /**
     * @Route("/logout", name="api_logout", methods={"POST"})
     */
    public function logout(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['message' => 'Email requis pour la déconnexion'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            $isEtudiant = false;
            foreach ($user->getEtudiants() as $etudiant) {
                if ($etudiant->isStatus()) {
                    $isEtudiant = true;
                    break;
                }
            }
            if ($isEtudiant) {
                $user->setOnlineStatus('OFFLINE');
                $user->setUpdatedAt(new \DateTimeImmutable());
                $this->doctrine->getManager()->persist($user);
                $this->doctrine->getManager()->flush();
                return $this->json(['message' => 'Déconnexion réussie']);
            }
        }

        return $this->json(['message' => 'Utilisateur non trouvé ou non autorisé'], Response::HTTP_NOT_FOUND);
    }
}