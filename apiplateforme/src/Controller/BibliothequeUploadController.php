<?php

namespace App\Controller;

use App\Entity\Bibliotheque;
use App\Repository\EcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsController]
class BibliothequeUploadController extends AbstractController
{
    private $entityManager;
    private $ecRepository;
    private $uploadHandler;
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        EcRepository $ecRepository,
        UploadHandler $uploadHandler,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->ecRepository = $ecRepository;
        $this->uploadHandler = $uploadHandler;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $type = $request->request->get('type');
        $ecId = $request->request->get('ec');

        if (!$file || !$titre || !$type || !$ecId) {
            return new Response(
                json_encode(['error' => 'Missing required fields']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $ec = $this->ecRepository->find($ecId);
        if (!$ec) {
            return new Response(
                json_encode(['error' => 'EC not found']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $bibliotheque = new Bibliotheque();
        $bibliotheque->setTitre($titre);
        $bibliotheque->setType($type);
        $bibliotheque->setEc($ec);
        $bibliotheque->setMention($ec->getParcours()->getMention());
        $bibliotheque->setParcours($ec->getParcours());
        $bibliotheque->setUser($this->getUser());
        $bibliotheque->setFile($file);

        // Gérer l'upload avec VichUploader
        $this->uploadHandler->upload($bibliotheque, 'file');

        $this->entityManager->persist($bibliotheque);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($bibliotheque, 'json', ['groups' => ['bibliotheque:read']]);

        return new Response(
            $data,
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json']
        );
    }
}