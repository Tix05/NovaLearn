<?php

namespace App\Controller;

use App\Entity\FichierSupport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class FichierSupportUploadController extends AbstractController
{
    public function __invoke(Request $request): FichierSupport
    {
        $uploadedFile = $request->files->get('file');
        $type = $request->request->get('type');

        // Validation selon le type
        if (in_array($type, [FichierSupport::TYPE_FICHIER, FichierSupport::TYPE_AUDIO]) && !$uploadedFile) {
            throw new BadRequestHttpException('Un fichier est requis pour ce type');
        }

        if (in_array($type, [FichierSupport::TYPE_LIEN, FichierSupport::TYPE_VIDEO]) && !$request->request->get('url')) {
            throw new BadRequestHttpException('Une URL est requise pour ce type');
        }

        $fichierSupport = new FichierSupport();
        $fichierSupport->setTitre($request->request->get('titre'));
        $fichierSupport->setType($type);
        $fichierSupport->setDescription($request->request->get('description'));
        $fichierSupport->setEstPublique($request->request->get('est_publique', true));
        $fichierSupport->setAuteur($this->getUser());

        // Gérer l'EC
        if ($ecId = $request->request->get('ec')) {
            // Récupérer l'entité EC depuis l'ID et faire setEc()
        }

        // Gestion du fichier uploadé
        if ($uploadedFile) {
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/supports';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '', $originalName);
            $fileName = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

            try {
                $uploadedFile->move($uploadDir, $fileName);
                $fichierSupport->setFichier('/uploads/supports/'.$fileName);
            } catch (\Exception $e) {
                throw new BadRequestHttpException('Échec de l\'upload du fichier');
            }
        } elseif ($url = $request->request->get('url')) {
            $fichierSupport->setUrl($url);
        }

        return $fichierSupport;
    }
}