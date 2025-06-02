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

    // Récupérer le parcours de l'utilisateur
    $etudiant = $this->entityManager->getRepository(Etudiant::class)->findOneBy(['user' => $user]);
    $prof = $this->entityManager->getRepository(Prof::class)->findOneBy(['user' => $user]);

    // Pour les étudiants
    if ($userRole === 'ETUDIANT' && $etudiant) {
        $parcours = $etudiant->getParcours();
        if ($parcours) {
            // Récupérer les groupes liés au parcours
            $groups = [$parcours];
            foreach ($groups as $group) {
                $result[] = [
                    'type' => 'GROUP',
                    'id' => $group->getId(),
                    'name' => $group->getName() ?? 'Groupe inconnu',
                    'avatar' => null,
                    'role' => 'GROUPE_FILIERE',
                    'isOnline' => false,
                    'conversationId' => $this->getExistingGroupConversationId($user, $group),
                ];
            }

            // Récupérer les professeurs liés au parcours via les EC
            $profs = $this->entityManager->getRepository(Prof::class)->createQueryBuilder('p')
                ->join('p.ecs', 'ec')
                ->join('ec.ue', 'ue')
                ->join('ue.mention', 'm')
                ->join('m.parcours', 'par')
                ->where('par.id = :parcoursId')
                ->setParameter('parcoursId', $parcours->getId())
                ->getQuery()
                ->getResult();

            // Convertir les Prof en User
            $profUsers = [];
            foreach ($profs as $prof) {
                $profUser = $prof->getUser();
                if ($profUser) {
                    $profUsers[] = $profUser;
                }
            }

            // Récupérer les administrateurs
            $admins = $this->userRepository->findByRole('ROLE_ADMIN');

            // Ajouter les professeurs et administrateurs comme partenaires
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
        }
    }
    // Pour les professeurs
    elseif ($userRole === 'PROFESSEUR' && $prof) {
        // Récupérer les parcours liés aux EC enseignés par le professeur
        $parcoursList = $this->entityManager->getRepository(Parcours::class)
            ->createQueryBuilder('p')
            ->join('p.ues', 'ue')
            ->join('ue.ecs', 'ec')
            ->where('ec.prof = :prof')
            ->setParameter('prof', $prof)
            ->getQuery()
            ->getResult();

        // Ajouter les groupes liés aux parcours
        foreach ($parcoursList as $parcours) {
            $result[] = [
                'type' => 'GROUP',
                'id' => $parcours->getId(),
                'name' => $parcours->getName() ?? 'Groupe inconnu',
                'avatar' => null,
                'role' => 'GROUPE_FILIERE',
                'isOnline' => false,
                'conversationId' => $this->getExistingGroupConversationId($user, $parcours),
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

        // Convertir les Etudiant en User
        $etudiantUsers = [];
        foreach ($etudiants as $etudiant) {
            $etudiantUser = $etudiant->getUser();
            if ($etudiantUser) {
                $etudiantUsers[] = $etudiantUser;
            }
        }

        // Ajouter les administrateurs
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
    } 
    // Pour les administrateurs
    elseif ($userRole === 'ADMIN') {
        // Récupérer tous les groupes (parcours)
        $groups = $this->entityManager->getRepository(Parcours::class)->findAll();
        foreach ($groups as $group) {
            $result[] = [
                'type' => 'GROUP',
                'id' => $group->getId(),
                'name' => $group->getName() ?? 'Groupe inconnu',
                'avatar' => null,
                'role' => 'GROUPE_FILIERE',
                'isOnline' => false,
                'conversationId' => $this->getExistingGroupConversationId($user, $group),
            ];
        }

        // Récupérer tous les utilisateurs sauf l'admin lui-même
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

    private function getExistingGroupConversationId(User $user, Parcours $group): ?int
    {
        $conversations = $this->conversationRepository->findBy(['parcours' => $group, 'type' => Conversation::TYPE_GROUPE_FILIERE]);
        foreach ($conversations as $conversation) {
            foreach ($conversation->getParticipants() as $participant) {
                if ($participant->getUser()->getId() === $user->getId()) {
                    return $conversation->getId();
                }
            }
        }
        return null;
    }

    public function createConversation(User $creator, ?User $recipient = null, ?Parcours $parcours = null): Conversation
    {
        if (!$creator) {
            throw new \InvalidArgumentException('Créateur de la conversation requis');
        }

        $conversation = new Conversation();
        $conversation->setCreatedBy($creator);

        if ($parcours) {
            $conversation->setType(Conversation::TYPE_GROUPE_FILIERE);
            $conversation->setSujet($parcours->getName() ?? 'Groupe sans nom');
            $conversation->setParcours($parcours);
        } else {
            $conversation->setType(Conversation::TYPE_PRIVEE);
            $conversation->setSujet('Conversation privée avec ' . ($recipient ? $recipient->getName() : 'Utilisateur'));
        }

        $creatorParticipant = new ParticipantConversation();
        $creatorParticipant->setUser($creator);
        $creatorParticipant->setRole(ParticipantConversation::ROLE_MEMBRE);
        $creatorParticipant->setConversation($conversation);
        $conversation->addParticipant($creatorParticipant);
        $this->entityManager->persist($creatorParticipant);

        if ($recipient) {
            $recipientParticipant = new ParticipantConversation();
            $recipientParticipant->setUser($recipient);
            $recipientParticipant->setRole(ParticipantConversation::ROLE_MEMBRE);
            $recipientParticipant->setConversation($conversation);
            $conversation->addParticipant($recipientParticipant);
            $this->entityManager->persist($recipientParticipant);
        } elseif ($parcours) {
            $etudiants = $this->entityManager->getRepository(User::class)->findByParcours($parcours);
            foreach ($etudiants as $etudiant) {
                if ($etudiant->getId() !== $creator->getId()) {
                    $participant = new ParticipantConversation();
                    $participant->setUser($etudiant);
                    $participant->setRole(ParticipantConversation::ROLE_MEMBRE);
                    $participant->setConversation($conversation);
                    $conversation->addParticipant($participant);
                    $this->entityManager->persist($participant);
                }
            }
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