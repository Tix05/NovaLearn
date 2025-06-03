<?php

namespace App\Controller\Api;

use App\Entity\Conversation;
use App\Entity\User;
use App\Entity\Parcours;
use App\Service\MessagingService;
use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/messaging")
 */
class MessagingController extends AbstractController
{
    private $messagingService;
    private $security;

    public function __construct(MessagingService $messagingService, Security $security)
    {
        $this->messagingService = $messagingService;
        $this->security = $security;
    }

    /**
     * @Route("/conversations", name="api_messaging_conversations", methods={"GET", "OPTIONS"})
     */
    public function getConversations(Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], 204);
        }

        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        try {
            $partners = $this->messagingService->getPotentialConversationPartners($user);

            $data = [];

            foreach ($partners as $partner) {
                if (!isset($partner['id']) || !isset($partner['name']) || !isset($partner['role'])) {
                    continue;
                }

                $data[] = [
                    'id' => $partner['conversationId'] ?? null,
                    'sujet' => $partner['type'] === 'GROUP' ? $partner['name'] : null,
                    'type' => $partner['type'] === 'GROUP' ? $partner['role'] : 'PRIVEE',
                    'parcoursId' => $partner['type'] === 'GROUP' ? $partner['id'] : null,
                    'participants' => [
                        [
                            'id' => $partner['id'],
                            'name' => $partner['name'],
                            'avatar' => $partner['avatar'] ?? null,
                            'role' => $partner['role'],
                            'isOnline' => $partner['isOnline'] ?? false,
                            'onlineStatus' => $partner['isOnline'] ? 'ONLINE' : 'OFFLINE',
                        ],
                    ],
                    'unreadCount' => 0,
                    'lastMessage' => null,
                ];
            }

            return $this->json($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/messages/{conversationId}", name="api_messaging_messages", methods={"GET"})
     */
    public function getMessages(int $conversationId): JsonResponse
    {
        try {
            $conversation = $this->getDoctrine()->getRepository(Conversation::class)->find($conversationId);
            if (!$conversation) {
                return $this->json(['error' => 'Conversation non trouvée'], 404);
            }

            $messages = $conversation->getMessages()->map(function ($message) {
                $expediteur = $message->getExpediteur();
                if (!$expediteur) {
                    throw new \RuntimeException('Expéditeur du message non défini');
                }

                $dateEnvoi = $message->getDateEnvoi();
                $formattedDate = $dateEnvoi ? $dateEnvoi->format('Y-m-d H:i:s') : (new \DateTime())->format('Y-m-d H:i:s');

                return [
                    'id' => $message->getId(),
                    'contenu' => $message->getContenu(),
                    'expediteur' => [
                        'id' => $expediteur->getId(),
                        'name' => $expediteur->getName() ?? 'Utilisateur inconnu',
                    ],
                    'date' => $formattedDate,
                    'lu' => $message->getLu(),
                ];
            })->toArray();

            return $this->json($messages);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la récupération des messages: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/send", name="api_messaging_send", methods={"POST"})
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $conversationId = $data['conversationId'] ?? null;
        $recipientId = $data['recipientId'] ?? null;
        $parcoursId = $data['parcoursId'] ?? null;
        $type = $data['type'] ?? null;
        $contenu = $data['contenu'] ?? '';

        if (!$contenu) {
            return $this->json(['error' => 'Contenu du message requis'], 400);
        }

        try {
            $conversation = null;
            if ($conversationId) {
                $conversation = $this->getDoctrine()->getRepository(Conversation::class)->find($conversationId);
                if (!$conversation) {
                    return $this->json(['error' => 'Conversation non trouvée'], 404);
                }
            } elseif ($recipientId || $parcoursId) {
                if ($recipientId) {
                    $recipient = $this->getDoctrine()->getRepository(User::class)->find($recipientId);
                    if (!$recipient) {
                        return $this->json(['error' => 'Destinataire non trouvé'], 404);
                    }
                    $conversation = $this->messagingService->createConversation($user, $recipient);
                } elseif ($parcoursId) {
                    $parcours = $this->getDoctrine()->getRepository(Parcours::class)->find($parcoursId);
                    if (!$parcours) {
                        return $this->json(['error' => 'Parcours non trouvé'], 404);
                    }
                    $conversation = $this->messagingService->createConversation($user, null, $parcours, $type ?: Conversation::TYPE_GROUPE_FILIERE);
                }
            } else {
                return $this->json(['error' => 'ConversationId, recipientId ou parcoursId requis'], 400);
            }

            $message = $this->messagingService->sendMessage($conversation, $contenu, $user);

            return $this->json([
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'expediteur' => [
                    'id' => $message->getExpediteur()->getId(),
                    'name' => $message->getExpediteur()->getName() ?? 'Utilisateur inconnu',
                ],
                'date' => $message->getDateEnvoi()->format('Y-m-d H:i:s'),
                'conversationId' => $conversation->getId(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'envoi du message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/mark-read/{messageId}", name="api_messaging_mark_read", methods={"POST"})
     */
    public function markMessageAsRead(int $messageId): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        try {
            $message = $this->getDoctrine()->getRepository(Message::class)->find($messageId);
            if (!$message) {
                return $this->json(['error' => 'Message non trouvé'], 404);
            }

            $this->messagingService->markMessageAsRead($message, $user);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du marquage du message: ' . $e->getMessage()], 500);
        }
    }
}