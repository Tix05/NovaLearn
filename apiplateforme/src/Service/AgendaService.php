<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\EtudiantExamenStatut;
use App\Repository\AgendaRepository;
use Doctrine\ORM\EntityManagerInterface;

class AgendaService
{
    private $agendaRepository;
    private $entityManager;

    public function __construct(AgendaRepository $agendaRepository, EntityManagerInterface $entityManager)
    {
        $this->agendaRepository = $agendaRepository;
        $this->entityManager = $entityManager;
    }

    public function getStudentAgendaData(Etudiant $etudiant)
    {
        $mention = $etudiant->getMention();
        $parcours = $etudiant->getParcours();
        $niveau = $etudiant->getNiveau();

        $mentionId = $mention ? $mention->getId() : null;
        $parcoursId = $parcours ? $parcours->getId() : null;
        $niveauId = $niveau ? $niveau->getId() : null;

        $examens = $this->agendaRepository->findByTypeAndFilters(
            'EXAMEN',
            $mentionId,
            $parcoursId,
            $niveauId
        );

        // Filtrer les examens soumis ou abandonnés
        $filteredExamens = array_filter($examens, function ($agenda) use ($etudiant) {
            $examen = $agenda->getExamens()->first();
            if (!$examen) {
                return true;
            }
            $statutExamen = $this->entityManager
                ->getRepository(EtudiantExamenStatut::class)
                ->findOneBy(['etudiant' => $etudiant, 'examen' => $examen]);
            return !$statutExamen || $statutExamen->getStatut() === 'EN_COURS';
        });

        $cours = $this->agendaRepository->findByTypeAndFilters(
            'COURS',
            $mentionId,
            $parcoursId,
            $niveauId
        );

        $evenements = $this->agendaRepository->findByTypeAndFilters(
            'EVENEMENT',
            $mentionId,
            $parcoursId,
            $niveauId
        );

        return [
            'cours' => $this->formatAgendaItems($cours),
            'examens' => $this->formatAgendaItems($filteredExamens),
            'evenements' => $this->formatAgendaItems($evenements)
        ];
    }

    private function formatAgendaItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'titre' => $item->getTitre(),
                'description' => $item->getDescription(),
                'date' => $item->getDate()->format('c'), // ISO 8601 format for frontend
                'type' => $item->getType(),
                'mention' => $item->getMention() ? $item->getMention()->getName() : null,
                'parcours' => $item->getParcours() ? $item->getParcours()->getName() : null,
                'niveau' => $item->getNiveau() ? $item->getNiveau()->getNom() : null,
                'nom_auteur' => $item->getNomAuteur() ?: 'Anonyme',
                'image' => $item->getImage() ? '/uploads/agenda/' . $item->getImage() : null,
                'video' => $item->getVideo() ? '/uploads/agenda/' . $item->getVideo() : null,
                'examenId' => $item->getExamens()->first() ? $item->getExamens()->first()->getId() : null,
            ];
        }, $items);
    }
}