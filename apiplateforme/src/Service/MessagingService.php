<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Entity\Message;
use App\Entity\ParticipantConversation;
use App\Entity\User;
use App\Entity\Parcours;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessagingService
{
    private $conversationRepository;
    private $messageRepository;
    private $userRepository;
    private $entityManager;

    public function __construct(
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getConversationsForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }
        return $this->conversationRepository->findByParticipant($user);
    }

    public function getPotentialConversationPartners(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $result = [];
        $userRole = $this->getUserRole($user);

        // Pour les enseignants
        if ($userRole === 'PROFESSEUR') {
            $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);
            if ($prof) {
                try {
                    // Récupérer les parcours liés aux EC enseignés par le professeur via UeParcours
                    $parcoursList = $this->entityManager->getRepository(Parcours::class)
                        ->createQueryBuilder('p')
                        ->join('p.ueParcours', 'up')
                        ->join('up.ue', 'ue')
                        ->join('ue.ecs', 'ec')
                        ->where('ec.prof = :prof')
                        ->setParameter('prof', $prof)
                        ->getQuery()
                        ->getResult();

                    // Ajouter les groupes de filière partagés avec les étudiants
                    foreach ($parcoursList as $parcours) {
                        $result[] = [
                            'type' => 'GROUP',
                            'id' => $parcours->getId(),
                            'name' => $parcours->getName() ?? 'Groupe inconnu',
                            'avatar' => null,
                            'role' => 'GROUPE_FILIERE',
                            'isOnline' => false,
                            'conversationId' => $this->getExistingGroupConversationId($user, $parcours, Conversation::TYPE_GROUPE_FILIERE),
                        ];
                    }

                    // Ajouter le groupe des professeurs du même parcours
                    foreach ($parcoursList as $parcours) {
                        $result[] = [
                            'type' => 'GROUP',
                            'id' => $parcours->getId() . '_prof',
                            'name' => 'Professeurs ' . ($parcours->getName() ?? 'Groupe inconnu'),
                            'avatar' => null,
                            'role' => 'GROUPE_PROFESSEUR_FILIERE',
                            'isOnline' => false,
                            'conversationId' => $this->getExistingGroupConversationId($user, $parcours, Conversation::TYPE_GROUPE_PROFESSEUR_FILIERE),
                        ];
                    }

                    // Récupérer les étudiants des parcours
                    $etudiants = $this->entityManager->getRepository(Etudiant::class)
                        ->createQueryBuilder('e')
                        ->join('e.parcours', 'p')
                        ->where('p IN (:parcoursList)')
                        ->setParameter('parcoursList', $parcoursList)
                        ->getQuery()
                        ->getResult();

                    // Convertir les étudiants en utilisateurs
                    $etudiantUsers = [];
                    foreach ($etudiants as $etudiant) {
                        $etudiantUser = $etudiant->getUser();
                        if ($etudiantUser) {
                            $etudiantUsers[] = $etudiantUser;
                        }
                    }

                    // Récupérer les administrateurs
                    $admins = $this->userRepository->findByRole('ROLE_ADMIN');

                    // Ajouter les étudiants et administrateurs comme partenaires
                    $partners = array_merge($etudiantUsers, $admins);
                    foreach ($partners as $partner) {
                        if ($partner->getId() !== $user->getId()) {
                            $result[] = [
                                'type' => 'USER',
                                'id' => $partner->getId(),
                                'name' => $partner->getName() ?? 'Utilisateur inconnu',
                                'avatar' => $partner->getAvatar(),
                                'role' => $this->getUserRole($partner),
                                'isOnline' => $this->getOnlineStatus($partner),
                                'conversationId' => $this->getExistingConversationId($user, $partner),
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    error_log('Erreur dans getPotentialConversationPartners (PROFESSEUR): ' . $e->getMessage());
                    return [];
                }
            }
        }
        // Pour les étudiants
        elseif ($userRole === 'ETUDIANT') {
            $etudiant = $this->entityManager->getRepository(Etudiant::class)->findOneBy(['user' => $user]);
            if ($etudiant) {
                $parcours = $etudiant->getParcours();
                if ($parcours) {
                    $groups = [$parcours];
                    foreach ($groups as $group) {
                        $result[] = [
                            'type' => 'GROUP',
                            'id' => $group->getId(),
                            'name' => $group->getName() ?? 'Groupe inconnu',
                            'avatar' => null,
                            'role' => 'GROUPE_FILIERE',
                            'isOnline' => false,
                            'conversationId' => $this->getExistingGroupConversationId($user, $group, Conversation::TYPE_GROUPE_FILIERE),
                        ];
                    }

                    try {
                        $profs = $this->entityManager->getRepository(Prof::class)->createQueryBuilder('p')
                            ->join('p.ecs', 'ec')
                            ->join('ec.ue', 'ue')
                            ->join('ue.ueParcours', 'up')
                            ->join('up.parcours', 'par')
                            ->where('par.id = :parcoursId')
                            ->setParameter('parcoursId', $parcours->getId())
                            ->getQuery()
                            ->getResult();

                        $profUsers = [];
                        foreach ($profs as $prof) {
                            $profUser = $prof->getUser();
                            if ($profUser) {
                                $profUsers[] = $profUser;
                            }
                        }

                        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
                        $partners = array_merge($profUsers, $admins);
                        foreach ($partners as $partner) {
                            if ($partner->getId() !== $user->getId()) {
                                $result[] = [
                                    'type' => 'USER',
                                    'id' => $partner->getId(),
                                    'name' => $partner->getName() ?? 'Utilisateur inconnu',
                                    'avatar' => $partner->getAvatar(),
                                    'role' => $this->getUserRole($partner),
                                    'isOnline' => $this->getOnlineStatus($partner),
                                    'conversationId' => $this->getExistingConversationId($user, $partner),
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        error_log('Erreur dans getPotentialConversationPartners (ETUDIANT): ' . $e->getMessage());
                        return [];
                    }
                }
            }
        }

        elseif ($userRole === 'ADMIN') {
            $users = $this->userRepository->findAllExcept($user->getId());
            foreach ($users as $partner) {
                $result[] = [
                    'type' => 'USER',
                    'id' => $partner->getId(),
                    'name' => $partner->getName() ?? 'Utilisateur inconnu',
                    'avatar' => $partner->getAvatar(),
                    'role' => $this->getUserRole($partner),
                    'isOnline' => $this->getOnlineStatus($partner),
                    'conversationId' => $this->getExistingConversationId($user, $partner),
                ];
            }
        }

        return $result;
    }

    private function getUserRole(User $user): string
    {
        if ($user->getProfs()->count() > 0) {
            return 'PROFESSEUR';
        }
        if ($user->getEtudiants()->count() > 0) {
            return 'ETUDIANT';
        }
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return 'ADMIN';
        }
        return 'MEMBRE';
    }

    private function getExistingConversationId(User $user, User $partner): ?int
    {
        $conversations = $this->conversationRepository->findByParticipant($user);
        foreach ($conversations as $conversation) {
            if ($conversation->getType() === Conversation::TYPE_PRIVEE) {
                $participants = $conversation->getParticipants();
                if ($participants->count() === 2) {
                    foreach ($participants as $participant) {
                        if ($participant->getUser()->getId() === $partner->getId()) {
                            return $conversation->getId();
                        }
                    }
                }
            }
        }
        return null;
    }

    private function getExistingGroupConversationId(User $user, Parcours $group, string $type): ?int
    {
        $conversations = $this->conversationRepository->findBy(['parcours' => $group, 'type' => $type]);
        foreach ($conversations as $conversation) {
            foreach ($conversation->getParticipants() as $participant) {
                if ($participant->getUser()->getId() === $user->getId()) {
                    return $conversation->getId();
                }
            }
        }
        return null;
    }

    public function createConversation(User $creator, ?User $recipient = null, ?Parcours $parcours = null, ?string $type = null): Conversation
    {
        if (!$creator) {
            throw new \InvalidArgumentException('Créateur de la conversation requis');
        }

        $conversation = new Conversation();
        $conversation->setCreatedBy($creator);

        if ($parcours && $type === Conversation::TYPE_GROUPE_FILIERE) {
            $conversation->setType(Conversation::TYPE_GROUPE_FILIERE);
            $conversation->setSujet($parcours->getName() ?? 'Groupe sans nom');
            $conversation->setParcours($parcours);

            try {
                // Ajouter tous les étudiants du parcours
                $etudiants = $this->entityManager->getRepository(Etudiant::class)->findBy(['parcours' => $parcours]);

                // Ajouter tous les professeurs liés au parcours via UeParcours
                $profs = $this->entityManager->getRepository(Prof::class)
                    ->createQueryBuilder('p')
                    ->join('p.ecs', 'ec')
                    ->join('ec.ue', 'ue')
                    ->join('ue.ueParcours', 'up')
                    ->join('up.parcours', 'par')
                    ->where('par.id = :parcoursId')
                    ->setParameter('parcoursId', $parcours->getId())
                    ->getQuery()
                    ->getResult();

                // Fusionner les utilisateurs (étudiants et professeurs)
                $users = [];
                foreach ($etudiants as $etudiant) {
                    $user = $etudiant->getUser();
                    if ($user && $user->getId() !== $creator->getId()) {
                        $users[] = $user;
                    }
                }
                foreach ($profs as $prof) {
                    $user = $prof->getUser();
                    if ($user && $user->getId() !== $creator->getId()) {
                        $users[] = $user;
                    }
                }

                // Ajouter les utilisateurs comme participants
                foreach ($users as $user) {
                    $participant = new ParticipantConversation();
                    $participant->setUser($user);
                    $participant->setRole(ParticipantConversation::ROLE_MEMBRE);
                    $participant->setConversation($conversation);
                    $conversation->addParticipant($participant);
                    $this->entityManager->persist($participant);
                }

                // Vérifier si des participants ont été ajoutés
                if ($conversation->getParticipants()->isEmpty()) {
                    error_log('Aucun participant ajouté au groupe de filière pour le parcours ID: ' . $parcours->getId());
                }
            } catch (\Exception $e) {
                error_log('Erreur dans createConversation (GROUPE_FILIERE): ' . $e->getMessage());
                throw new \Exception('Erreur lors de la création du groupe de filière: ' . $e->getMessage());
            }
        } elseif ($parcours && $type === Conversation::TYPE_GROUPE_PROFESSEUR_FILIERE) {
            $conversation->setType(Conversation::TYPE_GROUPE_PROFESSEUR_FILIERE);
            $conversation->setSujet('Professeurs ' . ($parcours->getName() ?? 'Groupe sans nom'));
            $conversation->setParcours($parcours);

            try {
                // Ajouter tous les professeurs du parcours
                $profs = $this->entityManager->getRepository(Prof::class)
                    ->createQueryBuilder('p')
                    ->join('p.ecs', 'ec')
                    ->join('ec.ue', 'ue')
                    ->join('ue.ueParcours', 'up')
                    ->join('up.parcours', 'par')
                    ->where('par.id = :parcoursId')
                    ->setParameter('parcoursId', $parcours->getId())
                    ->getQuery()
                    ->getResult();

                foreach ($profs as $prof) {
                    $user = $prof->getUser();
                    if ($user && $user->getId() !== $creator->getId()) {
                        $participant = new ParticipantConversation();
                        $participant->setUser($user);
                        $participant->setRole(ParticipantConversation::ROLE_MEMBRE);
                        $participant->setConversation($conversation);
                        $conversation->addParticipant($participant);
                        $this->entityManager->persist($participant);
                    }
                }
            } catch (\Exception $e) {
                error_log('Erreur dans createConversation (GROUPE_PROFESSEUR_FILIERE): ' . $e->getMessage());
                throw new \Exception('Erreur lors de la création du groupe de professeurs: ' . $e->getMessage());
            }
        } else {
            $conversation->setType(Conversation::TYPE_PRIVEE);
            $conversation->setSujet('Conversation privée avec ' . ($recipient ? $recipient->getName() : 'Utilisateur'));
        }

        // Ajouter le créateur comme participant
        $creatorParticipant = new ParticipantConversation();
        $creatorParticipant->setUser($creator);
        $creatorParticipant->setRole(ParticipantConversation::ROLE_MEMBRE);
        $creatorParticipant->setConversation($conversation);
        $conversation->addParticipant($creatorParticipant);
        $this->entityManager->persist($creatorParticipant);

        if ($recipient && !$parcours) {
            $recipientParticipant = new ParticipantConversation();
            $recipientParticipant->setUser($recipient);
            $recipientParticipant->setRole(ParticipantConversation::ROLE_MEMBRE);
            $recipientParticipant->setConversation($conversation);
            $conversation->addParticipant($recipientParticipant);
            $this->entityManager->persist($recipientParticipant);
        }

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

    public function sendMessage(Conversation $conversation, string $contenu, User $expediteur): Message
    {
        if (!$conversation || !$expediteur) {
            throw new \InvalidArgumentException('Conversation ou expéditeur requis');
        }

        $message = new Message();
        $message->setConversation($conversation)
                ->setExpediteur($expediteur)
                ->setContenu($contenu)
                ->setDateEnvoi(new \DateTime())
                ->setLu(false);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function markMessageAsRead(Message $message, User $user): void
    {
        if ($message->getExpediteur() !== $user && !$message->getLu()) {
            $message->setLu(true);
            $this->entityManager->flush();
        }
    }

    public function getOnlineStatus(User $user): bool
    {
        return $user->getOnlineStatus() === 'ONLINE';
    }
}