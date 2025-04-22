<?php

namespace App\Controller;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class DocumentUploadController extends AbstractController
{
    public function __invoke(Request $request): Document
    {
        // 1. Vérifier le fichier
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('Aucun fichier téléchargé');
        }

        // 2. Valider le type de fichier
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            throw new BadRequestHttpException('Type de fichier non autorisé');
        }

        // 3. Préparer le répertoire
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/documents';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // 4. Générer un nom de fichier sécurisé
        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalName
        );
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // 5. Déplacer le fichier
        try {
            $uploadedFile->move($uploadDir, $newFilename);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Échec du téléversement: '.$e->getMessage());
        }

        // 6. Créer et retourner l'entité Document
        $document = new Document();
        $document->setFilePath('/uploads/documents/'.$newFilename);
        $document->setTitre($request->request->get('titre'));
        $document->setTypeDocument($request->request->get('type_document'));
        $document->setUser($this->getUser());

        return $document;
    }
}