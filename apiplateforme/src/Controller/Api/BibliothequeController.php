<?php

namespace App\Controller\Api;

use App\Entity\FichierSupport;
use App\Entity\Bibliotheque;
use App\Repository\FichierSupportRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\EcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @Route("/api")
 */
class BibliothequeController extends AbstractController
{
    private $fichierSupportRepository;
    private $bibliothequeRepository;
    private $entityManager;
    private $security;
    private $logger;
    private $storage;

    public function __construct(
        FichierSupportRepository $fichierSupportRepository,
        BibliothequeRepository $bibliothequeRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
        StorageInterface $storage
    ) {
        $this->fichierSupportRepository = $fichierSupportRepository;
        $this->bibliothequeRepository = $bibliothequeRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->storage = $storage;
    }

    /**
     * @Route("/bibliotheques", name="api_bibliotheques", methods={"GET"})
     */
    public function getBibliothequeItems(): Response
    {
        $baseUrl = $this->getParameter('app.base_url');

        // Récupérer les fichiers de l'entité Bibliotheque
        $bibliothequeItems = $this->bibliothequeRepository->findBy(['status' => true]);

        // Récupérer les fichiers de type "document" de FichierSupport
        $fichierSupports = $this->fichierSupportRepository->findBy([
            'type' => FichierSupport::TYPE_FICHIER,
            'est_publique' => true,
        ]);

        // Fusionner les données
        $items = [];

        // Ajouter les éléments de Bibliotheque
        foreach ($bibliothequeItems as $item) {
            $type = ($item->getType() === 'document') ? 'leçon' : $item->getType();

            $items[] = [
                '@id' => '/api/bibliotheques/' . $item->getId(),
                'titre' => $item->getTitre(),
                'type' => $type,
                'fichier' => $item->getFichier() ? $baseUrl . '/Uploads/bibliotheque/' . $item->getFichier() : null,
                'mentionName' => $item->getMentionName(),
                'niveauNom' => $item->getNiveauNom(),
                'ecName' => $item->getEcName(),
                'status' => $item->getStatus(),
            ];
        }

        // Ajouter les éléments de FichierSupport
        foreach ($fichierSupports as $support) {
            $fileUrl = $support->getFichier()
                ? $baseUrl . '/Uploads/supports/' . $support->getFichier()
                : $support->getUrl();

            $items[] = [
                '@id' => '/api/fichier_supports/' . $support->getId(),
                'titre' => $support->getTitre(),
                'type' => $this->mapSupportType($support->getType()),
                'fichier' => $fileUrl,
                'mentionName' => $support->getEc()->getUe()->getMention()->getName(),
                'niveauNom' => $support->getEc()->getUe()->getSemestre()->getNiveau()->getNom(),
                'ecName' => $support->getEc()->getName(),
                'status' => $support->isEstPublique(),
            ];
        }

        return $this->json([
            'hydra:member' => $items,
            'hydra:totalItems' => count($items),
        ], Response::HTTP_OK, [], ['groups' => ['bibliotheque:list']]);
    }

    /**
     * @Route("/api/bibliotheques", name="api_create_bibliotheque", methods={"POST"})
     */
    public function createBibliothequeItem(Request $request): Response
    {
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $type = $request->request->get('type');
        $ecId = $request->request->get('ec');
        $parcoursName = $request->request->get('parcours');
        $status = $request->request->get('status') === '1';

        if (!$file || !$titre || !$type || !$ecId || !$parcoursName) {
            $this->logger->warning('Données manquantes', [
                'file' => $file ? 'present' : 'missing',
                'titre' => $titre,
                'type' => $type,
                'ec' => $ecId,
                'parcours' => $parcoursName,
            ]);
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $ec = $this->ecRepository->find($ecId);
        if (!$ec) {
            $this->logger->warning('EC non trouvé', ['ec_id' => $ecId]);
            return $this->json(['message' => 'EC non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $parcours = $this->entityManager->getRepository(Parcours::class)->findOneBy(['name' => $parcoursName]);
        if (!$parcours) {
            $this->logger->warning('Parcours non trouvé', ['parcours_name' => $parcoursName]);
            return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->error('Aucun utilisateur authentifié');
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
            $this->logger->error('Utilisateur non enseignant', ['user' => $user->getEmail()]);
            return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_FORBIDDEN);
        }

        $validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
        if (!in_array($type, $validTypes)) {
            $this->logger->warning('Type invalide', ['type' => $type]);
            return $this->json(['message' => 'Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice'], Response::HTTP_BAD_REQUEST);
        }

        $bibliotheque = new Bibliotheque();
        $bibliotheque->setTitre($titre);
        $bibliotheque->setType($type);
        $bibliotheque->setEc($ec);
        $bibliotheque->setMention($ec->getParcours()->getMention());
        $bibliotheque->setParcours($parcours);
        $bibliotheque->setUser($user); // Remplacer setAuteur par setUser
        $bibliotheque->setFile($file);
        $bibliotheque->setStatus($status);

        try {
            $this->uploadHandler->upload($bibliotheque, 'file');
            $this->entityManager->persist($bibliotheque);
            $this->entityManager->flush();

            $this->logger->info('Élément de bibliothèque créé avec succès', ['id' => $bibliotheque->getId(), 'titre' => $titre]);

            $data = $this->serializer->serialize($bibliotheque, 'json', ['groups' => ['bibliotheque:read']]);

            return new Response(
                $data,
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'élément de bibliothèque', [
                'error' => $e->getMessage(),
                'titre' => $titre,
            ]);
            return $this->json(['message' => 'Erreur lors de la création : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
 * @Route("/bibliotheques/{id}", name="api_update_bibliotheque", methods={"PUT"})
 */
public function updateBibliothequeItem(int $id, Request $request, EcRepository $ecRepository): Response
{
    $user = $this->security->getUser();
    if (!$user) {
        $this->logger->error('Aucun utilisateur authentifié');
        return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_OK);
    }

    if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
        $this->logger->error('Utilisateur non enseignant', ['user' => $user->getEmail()]);
        return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_OK);
    }

    $bibliotheque = $this->bibliothequeRepository->find($id);
    if (!$bibliotheque) {
        $this->logger->warning('Élément non trouvé', ['id' => $id]);
        return $this->json(['message' => 'Élément non trouvé'], Response::HTTP_OK);
    }

    $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
    if (!$prof || $bibliotheque->getEc()->getProf()->getId() !== $prof->getId()) {
        $this->logger->warning('Non autorisé à modifier cet élément', ['id' => $id, 'prof_id' => $prof ? $prof->getId() : null]);
        return $this->json(['message' => 'Non autorisé à modifier cet élément'], Response::HTTP_OK);
    }

    $data = $request->request->all();
    $ecId = $data['ec'] ?? null;
    $titre = $data['titre'] ?? null;
    $type = $data['type'] ?? null;
    $status = filter_var($data['status'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $parcoursName = $data['parcours'] ?? null;
    $file = $request->files->get('file');

    if (!$ecId || !$titre || !$type || !$parcoursName) {
        $this->logger->warning('Données manquantes', ['ec' => $ecId, 'titre' => $titre, 'type' => $type, 'parcours' => $parcoursName]);
        return $this->json(['message' => 'Données manquantes'], Response::HTTP_OK);
    }

    // Validation du type
    $validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
    if (!in_array($type, $validTypes)) {
        $this->logger->warning('Type invalide', ['type' => $type]);
        return $this->json(['message' => 'Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice'], Response::HTTP_OK);
    }

    $ec = $ecRepository->find($ecId);
    if (!$ec) {
        $this->logger->warning('EC non trouvé', ['ec_id' => $ecId]);
        return $this->json(['message' => 'EC non trouvé'], Response::HTTP_OK);
    }

    $parcours = $this->entityManager->getRepository(\App\Entity\Parcours::class)
        ->findOneBy(['name' => $parcoursName]);
    if (!$parcours) {
        $this->logger->warning('Parcours non trouvé', ['parcours_name' => $parcoursName]);
        return $this->json(['message' => 'Parcours non trouvé'], Response::HTTP_OK);
    }

    if ($ec->getProf()->getId() !== $prof->getId()) {
        $this->logger->warning('Non autorisé à modifier avec cet EC', ['ec_id' => $ecId, 'prof_id' => $prof->getId()]);
        return $this->json(['message' => 'Non autorisé à modifier avec cet EC'], Response::HTTP_OK);
    }

    $bibliotheque->setTitre($titre);
    $bibliotheque->setType($type);
    $bibliotheque->setEc($ec);
    $bibliotheque->setParcours($parcours);
    $bibliotheque->setStatus($status);
    if ($file) {
        $bibliotheque->setFile($file);
    }

    $this->entityManager->persist($bibliotheque);
    $this->entityManager->flush();

    $this->logger->info('Élément de bibliothèque modifié avec succès', ['id' => $bibliotheque->getId(), 'titre' => $titre]);

    return $this->json([
        'message' => 'Élément modifié avec succès',
        '@id' => '/api/bibliotheques/' . $bibliotheque->getId(),
        'titre' => $bibliotheque->getTitre(),
        'type' => $bibliotheque->getType(),
        'fichier' => $bibliotheque->getFichier() ? $this->getParameter('app.base_url') . '/Uploads/bibliotheque/' . $bibliotheque->getFichier() : null,
        'mentionName' => $bibliotheque->getMentionName(),
        'niveauNom' => $bibliotheque->getNiveauNom(),
        'ecName' => $bibliotheque->getEcName(),
        'status' => $bibliotheque->getStatus(),
    ], Response::HTTP_OK);
}

    /**
 * @Route("/bibliotheques/{id}", name="api_delete_bibliotheque", methods={"DELETE"})
 */
public function deleteBibliothequeItem(int $id): Response
{
    $user = $this->security->getUser();
    if (!$user) {
        $this->logger->error('Aucun utilisateur authentifié');
        return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_OK);
    }

    if (!in_array('ROLE_PROFESSEUR', $user->getRoles())) {
        $this->logger->error('Utilisateur non enseignant', ['user' => $user->getEmail()]);
        return $this->json(['message' => 'Utilisateur non enseignant'], Response::HTTP_OK);
    }

    $bibliotheque = $this->bibliothequeRepository->find($id);
    if (!$bibliotheque) {
        $this->logger->warning('Élément non trouvé', ['id' => $id]);
        return $this->json(['message' => 'Élément non trouvé'], Response::HTTP_OK);
    }

    $prof = $this->entityManager->getRepository(\App\Entity\Prof::class)->findOneBy(['user' => $user]);
    if (!$prof || $bibliotheque->getEc()->getProf()->getId() !== $prof->getId()) {
        $this->logger->warning('Non autorisé à supprimer cet élément', ['id' => $id, 'prof_id' => $prof ? $prof->getId() : null]);
        return $this->json(['message' => 'Non autorisé à supprimer cet élément'], Response::HTTP_OK);
    }

    // Supprimer le fichier associé avec VichUploader
    if ($bibliotheque->getFichier()) {
        $this->storage->remove($bibliotheque, 'file');
    }

    $this->entityManager->remove($bibliotheque);
    $this->entityManager->flush();

    $this->logger->info('Élément de bibliothèque supprimé avec succès', ['id' => $id]);

    return $this->json(['message' => 'Élément supprimé avec succès'], Response::HTTP_OK);
}

    private function mapSupportType($type)
    {
        $types = [
            FichierSupport::TYPE_FICHIER => 'leçon',
            FichierSupport::TYPE_VIDEO => 'video',
            FichierSupport::TYPE_AUDIO => 'audio',
            FichierSupport::TYPE_LIEN => 'lien',
        ];

        $type = str_replace("'", "", $type);
        return $types[$type] ?? 'document';
    }
}