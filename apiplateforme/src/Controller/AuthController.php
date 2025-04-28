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

        
        $token = $this->JWTManager->create($user);
        
        return $this->json([
            'token' => $token,
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(), // Bien renvoyer les rôles
            'etudiant' => $user->getEtudiants()->first() ? [
                'matricule' => $user->getEtudiants()->first()->getMatricule(),
                'mention' => $user->getEtudiants()->first()->getMention()->getName(),
                'parcours' => $user->getEtudiants()->first()->getParcours()->getName(),
                'niveau' => $user->getEtudiants()->first()->getNiveau()->getNom()
            ] : null
        ]);
    }
}