<?php

namespace App\Controller;

use App\Entity\Bibliotheque;
use App\Repository\EcRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

#[AsController]
class BibliothequeUploadController extends AbstractController
{
    private $entityManager;
    private $ecRepository;
    private $parcoursRepository;
    private $serializer;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        EcRepository $ecRepository,
        ParcoursRepository $parcoursRepository,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->ecRepository = $ecRepository;
        $this->parcoursRepository = $parcoursRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $type = $request->request->get('type');
        $ecId = $request->request->get('ec');
        $parcoursName = $request->request->get('parcours');
        $status = filter_var($request->request->get('status', true), FILTER_VALIDATE_BOOLEAN);

        // Validation des champs requis
        if (!$file || !$titre || !$type || !$ecId || !$parcoursName) {
            $this->logger->warning('Données manquantes', [
                'file' => $file ? 'present' : 'missing',
                'titre' => $titre,
                'type' => $type,
                'ec' => $ecId,
                'parcours' => $parcoursName,
            ]);
            return new Response(
                json_encode(['error' => 'Les champs file, titre, type, ec et parcours sont requis']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        // Vérifier si l'EC existe
        $ec = $this->ecRepository->find($ecId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $ecId]);
            return new Response(
                json_encode(['error' => 'EC non trouvé']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        // Vérifier si le parcours existe
        $parcours = $this->parcoursRepository->findOneBy(['name' => $parcoursName]);
        if (!$parcours) {
            $this->logger->warning('Parcours non trouvé', ['parcours_name' => $parcoursName]);
            return new Response(
                json_encode(['error' => 'Parcours non trouvé']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        // Vérifier l'utilisateur authentifié
        $user = $this->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return new Response(
                json_encode(['error' => 'Utilisateur non authentifié']),
                Response::HTTP_UNAUTHORIZED,
                ['Content-Type' => 'application/json']
            );
        }

        // Vérifier le rôle
        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non enseignant', ['user' => $user->getEmail()]);
            return new Response(
                json_encode(['error' => 'Utilisateur non enseignant']),
                Response::HTTP_FORBIDDEN,
                ['Content-Type' => 'application/json']
            );
        }

        // Valider le type
        $validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
        if (!in_array($type, $validTypes)) {
            $this->logger->warning('Type invalide', ['type' => $type]);
            return new Response(
                json_encode(['error' => 'Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        // Créer une nouvelle instance de Bibliotheque
        $bibliotheque = new Bibliotheque();
        $bibliotheque->setTitre($titre);
        $bibliotheque->setType($type);
        $bibliotheque->setEc($ec);
        $bibliotheque->setParcours($parcours);
        $bibliotheque->setMention($parcours->getMention());
        $bibliotheque->setUser($user);
        $bibliotheque->setStatus($status);

        // Gérer le fichier manuellement
        try {
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $this->getParameter('kernel.project_dir') . '/public/Uploads/bibliotheque/' . $fileName;
            $file->move($this->getParameter('kernel.project_dir') . '/public/Uploads/bibliotheque', $fileName);
            $bibliotheque->setFichier($fileName);
            $bibliotheque->setFile($file); // Nécessaire pour VichUploaderBundle
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'upload du fichier', [
                'error' => $e->getMessage(),
                'titre' => $titre,
            ]);
            return new Response(
                json_encode(['error' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }

        // Persister l'entité
        try {
            $this->entityManager->persist($bibliotheque);
            $this->entityManager->flush();

            $this->logger->info('Élément de bibliothèque créé avec succès', [
                'id' => $bibliotheque->getId(),
                'titre' => $titre,
            ]);

            $data = $this->serializer->serialize($bibliotheque, 'json', ['groups' => ['bibliotheque:read']]);

            return new Response(
                $data,
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la persistance de l\'élément de bibliothèque', [
                'error' => $e->getMessage(),
                'titre' => $titre,
            ]);
            return new Response(
                json_encode(['error' => 'Erreur lors de la création : ' . $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
}