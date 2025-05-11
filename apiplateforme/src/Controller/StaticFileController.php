<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class StaticFileController extends AbstractController
{
    /**
     * @Route("/uploads/supports/{filename}", name="static_support_file", methods={"GET", "OPTIONS"})
     */
    public function serveSupportFile(string $filename, Request $request): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Max-Age', '3600');
            return $response;
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/supports/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Fichier non trouvé: ' . $filename);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'mp4', 'webm', 'ogg', 'mp3', 'wav'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new NotFoundHttpException('Type de fichier non supporté: ' . $extension);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', mime_content_type($filePath));
        $disposition = $request->query->get('disposition', 'inline') === 'inline' ? 'inline' : 'attachment';
        $response->headers->set('Content-Disposition', $disposition . '; filename="' . $filename . '"');
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Accept-Ranges', 'bytes');

        return $response;
    }

    /**
     * @Route("/uploads/bibliotheque/{filename}", name="static_bibliotheque_file", methods={"GET", "OPTIONS"}, requirements={"filename"=".+"})
     * @Route("/uploads/bibliotheque/{filename<.+>}", name="static_bibliotheque_file_fallback", methods={"GET", "OPTIONS"})
     */
    public function serveBibliothequeFile(string $filename, Request $request): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Max-Age', '3600');
            return $response;
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/bibliotheque/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Fichier non trouvé: ' . $filename);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new NotFoundHttpException('Type de fichier non supporté: ' . $extension);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', mime_content_type($filePath));
        $disposition = $request->query->get('disposition', 'inline') === 'inline' ? 'inline' : 'attachment';
        $response->headers->set('Content-Disposition', $disposition . '; filename="' . $filename . '"');
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Accept-Ranges', 'bytes');

        return $response;
    }
}