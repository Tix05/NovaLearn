<?php

namespace App\Controller;

use App\Entity\FichierSupport;
use App\Repository\EcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FichierSupportUploadController extends AbstractController
{
    private $entityManager;
    private $ecRepository;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, EcRepository $ecRepository, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->ecRepository = $ecRepository;
        $this->security = $security;
    }

    /**
     * @Route("/fichier_supports/upload", name="fichier_support_upload", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $type = $request->request->get('type');
        $ecId = $request->request->get('ec');
        $description = $request->request->get('description');
        $estPublique = filter_var($request->request->get('est_publique', true), FILTER_VALIDATE_BOOLEAN);
        $url = $request->request->get('url');

        // Validation des champs requis
        if (!$titre || !$type || !$ecId) {
            return $this->json(['error' => 'Les champs titre, type et ec sont requis'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'EC existe
        $ec = $this->ecRepository->find($ecId);
        if (!$ec) {
            return $this->json(['error' => 'EC non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Créer une nouvelle instance de FichierSupport
        $fichierSupport = new FichierSupport();
        $fichierSupport->setTitre($titre);
        $fichierSupport->setType($type);
        $fichierSupport->setEc($ec);
        $fichierSupport->setDescription($description);
        $fichierSupport->setEstPublique($estPublique);
        $fichierSupport->setAuteur($this->security->getUser());

        // Gérer le fichier ou l'URL
        if ($type !== FichierSupport::TYPE_LIEN) {
            if (!$file) {
                return $this->json(['error' => 'Un fichier est requis pour ce type de support'], Response::HTTP_BAD_REQUEST);
            }
            $fichierSupport->setFile($file);
        } else {
            if (!$url) {
                return $this->json(['error' => 'Une URL est requise pour le type LIEN'], Response::HTTP_BAD_REQUEST);
            }
            $fichierSupport->setUrl($url);
        }

        // Persister l'entité
        $this->entityManager->persist($fichierSupport);
        $this->entityManager->flush();

        return $this->json($fichierSupport, Response::HTTP_CREATED, [], ['groups' => 'fichier_support:read']);
    }
}