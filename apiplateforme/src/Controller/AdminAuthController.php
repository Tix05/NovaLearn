<?php

namespace App\Controller;

use App\Entity\User;
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
class AdminAuthController extends AbstractController
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
     * @Route("/admin/login", name="api_admin_login", methods={"POST"})
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
        
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json([
                'message' => "Vous n'avez pas accès à l'espace admin"
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Mettre à jour le statut en ligne
        $user->setOnlineStatus('ONLINE');
        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();
        
        $token = $this->JWTManager->create($user);
        
        return $this->json([
            'token' => $token,
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(),
            'onlineStatus' => $user->getOnlineStatus()
        ]);
    }

    /**
     * @Route("/admin/logout", name="api_admin_logout", methods={"POST"})
     */
    public function logout(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['message' => 'Email requis pour la déconnexion'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setOnlineStatus('OFFLINE');
            $this->doctrine->getManager()->persist($user);
            $this->doctrine->getManager()->flush();
            return $this->json(['message' => 'Déconnexion réussie']);
        }

        return $this->json(['message' => 'Utilisateur non trouvé ou non autorisé'], Response::HTTP_NOT_FOUND);
    }
}