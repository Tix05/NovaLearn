<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification;
use App\Service\NotificationWebSocketService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

// Bootstrap the Symfony kernel
require dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die('Erreur: Le fichier .env est introuvable à ' . $envFile);
}
(new \Symfony\Component\Dotenv\Dotenv())->load($envFile);

// Define environment (try 'dev' to match console, can switch to 'prod' later)
$env = 'dev';
$debug = true;

// Define a basic Kernel class if not already defined
$kernelClass = class_exists(\App\Kernel::class) ? \App\Kernel::class : null;
if (!$kernelClass) {
    class AppKernel extends Kernel
    {
        use MicroKernelTrait;

        public function getProjectDir(): string
        {
            return dirname(__DIR__);
        }
    }
    $kernel = new AppKernel($env, $debug);
} else {
    $kernel = new \App\Kernel($env, $debug);
}

try {
    $kernel->boot();
} catch (\Exception $e) {
    die('Erreur lors du démarrage du kernel: ' . $e->getMessage() . "\n");
}

$container = $kernel->getContainer();

// Debug: List all services to verify container contents
$services = array_keys($container->getServiceIds());
echo "Services disponibles dans le conteneur:\n";
echo implode("\n", $services) . "\n";

// Verify that NotificationWebSocketService is available
if (!$container->has(NotificationWebSocketService::class)) {
    die(sprintf(
        "Erreur: Le service App\Service\NotificationWebSocketService n'est pas disponible.\n" .
        "Vérifiez que le fichier src/Service/NotificationWebSocketService.php existe et que le namespace est App\Service.\n" .
        "Assurez-vous que config/services.yaml contient la définition correcte.\n" .
        "Vérifiez également que la classe est autoloadée correctement (essayez 'composer dump-autoload').\n" .
        "Nombre de services dans le conteneur: %d\n",
        count($services)
    ));
}

// Verify that Doctrine is available
if (!$container->has('doctrine')) {
    die('Erreur: Le service Doctrine n\'est pas disponible. Vérifiez votre configuration Doctrine dans config/packages/doctrine.yaml.');
}

class NotificationServer implements MessageComponentInterface
{
    protected $clients;
    protected $entityManager;
    protected $notificationService;

    public function __construct(ContainerInterface $container, NotificationWebSocketService $notificationService)
    {
        $this->clients = new \SplObjectStorage();
        try {
            $this->entityManager = $container->get('doctrine')->getManager();
        } catch (\Exception $e) {
            die('Erreur lors de l\'initialisation de Doctrine: ' . $e->getMessage() . "\n");
        }
        $this->notificationService = $notificationService;
        echo "NotificationServer initialisé avec succès.\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $queryParams);
        $userId = $queryParams['userId'] ?? null;

        if ($userId) {
            $conn->userId = $userId;
            $this->notificationService->addClient($conn);
            echo "Nouvelle connexion: utilisateur {$userId} ({$conn->resourceId})\n";
        } else {
            echo "Connexion refusée: userId manquant.\n";
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (isset($data['action']) && $data['action'] === 'markAsRead') {
            $notificationId = $data['notificationId'] ?? null;
            if ($notificationId) {
                $notification = $this->entityManager->getRepository(Notification::class)->find($notificationId);
                if ($notification && $notification->getUser()->getId() == $from->userId) {
                    $notification->markAsRead();
                    $this->entityManager->flush();
                    echo "Notification {$notificationId} marquée comme lue pour l'utilisateur {$from->userId}.\n";
                } else {
                    echo "Erreur: Notification {$notificationId} non trouvée ou non autorisée pour l'utilisateur {$from->userId}.\n";
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->notificationService->removeClient($conn);
        echo "Connexion fermée: utilisateur {$conn->userId} ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur: {$e->getMessage()}\n";
        $conn->close();
    }

    public function sendNotification($userId, $notification)
    {
        foreach ($this->clients as $client) {
            if (isset($client->userId) && $client->userId == $userId) {
                $client->send(json_encode([
                    'id' => $notification->getId(),
                    'titre' => $notification->getTitre(),
                    'contenu' => $notification->getContenu(),
                    'type' => $notification->getType(),
                    'lien' => $notification->getLien(),
                    'dateCreation' => $notification->getDateCreation()->format('c'),
                    'lue' => $notification->isLue(),
                ]));
                echo "Notification envoyée à l'utilisateur {$userId}.\n";
            }
        }
    }
}

// Start the WebSocket server
try {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new NotificationServer(
                    $container,
                    $container->get(NotificationWebSocketService::class)
                )
            )
        ),
        8080
    );

    echo "Serveur WebSocket démarré sur ws://localhost:8080\n";
    $server->run();
} catch (\Exception $e) {
    die("Erreur lors du démarrage du serveur WebSocket: {$e->getMessage()}\n");
}