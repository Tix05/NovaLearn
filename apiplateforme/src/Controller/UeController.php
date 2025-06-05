<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\UeParcours;
use App\Entity\Parcours;
use App\Repository\UeRepository;
use App\Repository\ParcoursRepository;
use App\Repository\UeParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @Route("/api/ues")
 */
class UeController
{
    private $entityManager;
    private $ueRepository;
    private $parcoursRepository;
    private $ueParcoursRepository;
    private $serialize;

    public function __construct(
        EntityManagerInterface $entityManager,
        UeRepository $ueRepository,
        ParcoursRepository $parcoursRepository,
        UeParcoursRepository $ueParcoursRepository,
        SerializerInterface $serialize
    ) {
        $this->entityManager = $entityManager;
        $this->ueRepository = $ueRepository;
        $this->parcoursRepository = $parcoursRepository;
        $this->ueParcoursRepository = $ueParcoursRepository;
        $this->serialize = $serialize;
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $ue = $this->serialize->deserialize($request->getContent(), Ue::class, 'json');
            
            if (isset($data['parcours']) && is_array($data['parcours'])) {
                foreach ($data['parcours'] as $parcoursId) {
                    $parcours = $this->parcoursRepository->find($parcoursId);
                    if ($parcours) {
                        $ueParcours = new UeParcours();
                        $ueParcours->setUe($ue);
                        $ueParcours->setParcours($parcours);
                        $this->entityManager->persist($ueParcours);
                    }
                }
            }

            $this->entityManager->persist($ue);
            $this->entityManager->flush();

            return new JsonResponse($this->serialize->serialize($ue, 'json', ['groups' => 'ue:read']), 201, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $ue = $this->ueRepository->find($id);
            if (!$ue) {
                return new JsonResponse(['message' => 'Ue not found'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $this->serialize->deserialize($request->getContent(), Ue::class, 'json', ['object_to_populate' => $ue]);

            $existingUeParcours = $this->ueParcoursRepository->findBy(['ue' => $ue]);
            foreach ($existingUeParcours as $ueParcours) {
                $this->entityManager->remove($ueParcours);
            }

            if (isset($data['parcours']) && is_array($data['parcours'])) {
                foreach ($data['parcours'] as $parcoursId) {
                    $parcours = $this->parcoursRepository->find($parcoursId);
                    if ($parcours) {
                        $ueParcours = new UeParcours();
                        $ueParcours->setUe($ue);
                        $ueParcours->setParcours($parcours);
                        $this->entityManager->persist($ueParcours);
                    }
                }
            }

            $this->entityManager->flush();

            return new JsonResponse($this->serialize->serialize($ue, 'json', ['groups' => 'ue:read']), 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }
}