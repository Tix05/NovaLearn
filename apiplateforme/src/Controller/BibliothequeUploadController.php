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
            throw new BadRequestHttpException('Un fichier est requis');
        }

        // Validation du type de fichier
        $allowedMimeTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            throw new BadRequestHttpException('Type de fichier non autorisé');
        }

        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/bibliotheque';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalName
        );
        $fileName = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        try {
            $uploadedFile->move($uploadDir, $fileName);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Échec de l\'upload: '.$e->getMessage());
        }

        $bibliotheque = new Bibliotheque();
        $bibliotheque->setFichier('/uploads/bibliotheque/'.$fileName);
        $bibliotheque->setTitre($request->request->get('titre'));
        $bibliotheque->setType($request->request->get('type'));
        $bibliotheque->setUser($this->getUser());

        // Gérer l'EC
        if ($ecId = $request->request->get('ec')) {
            // Récupérer l'entité EC depuis l'ID et faire setEc()
        }

        return $bibliotheque;
    }
}