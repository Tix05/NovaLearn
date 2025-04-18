<?php

namespace App\Controller;

use App\Entity\Bibliotheque;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class BibliothequeUploadController extends AbstractController
{
    public function __invoke(Request $request): Bibliotheque
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $bibliotheque = new Bibliotheque();
        $bibliotheque->setFile($uploadedFile);
        $bibliotheque->setTitre($request->request->get('titre'));
        $bibliotheque->setType($request->request->get('type'));
        
        // Gérer l'EC (peut être passé comme ID)
        if ($ecId = $request->request->get('ec')) {
            // Récupérer l'entité EC depuis l'ID et faire setEc()
        }

        // Le fichier sera déplacé dans un EventSubscriber ou ici
        $fileName = uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move($this->getParameter('bibliotheque_directory'), $fileName);
        $bibliotheque->setFichier($fileName);

        return $bibliotheque;
    }
}