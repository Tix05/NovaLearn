<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\Agenda;
use App\Repository\AgendaRepository;

class AgendaService
{
    private $agendaRepository;

    public function __construct(AgendaRepository $agendaRepository)
    {
        $this->agendaRepository = $agendaRepository;
    }

    public function getStudentAgendaData(Etudiant $etudiant)
    {
        $mention = $etudiant->getMention();
        $parcours = $etudiant->getParcours();
        $niveau = $etudiant->getNiveau();

        $mentionId = $mention ? $mention->getId() : null;
        $parcoursId = $parcours ? $parcours->getId() : null;
        $niveauId = $niveau ? $niveau->getId() : null;

        // Récupérer les cours, examens et événements réels
        $cours = $this->agendaRepository->findByTypeAndFilters(
            'COURS',
            $mentionId,
            $parcoursId,
            $niveauId
        );

        $examens = $this->agendaRepository->findByTypeAndFilters(
            'EXAMEN',
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
            'examens' => $this->formatAgendaItems($examens),
            'evenements' => $this->formatAgendaItems($evenements)
        ];
    }

    private function formatAgendaItems(array $items): array
    {
        return array_map(function ($item) {
            $data = [
                'id' => $item->getId(),
                'titre' => $item->getTitre(),
                'date' => $item->getDate()->format('Y-m-d H:i:s'),
                'date_expiration' => $item->getDateExpiration()->format('Y-m-d H:i:s'),
                'description' => $item->getDescription(),
                'image' => $item->getImage(),
                'video' => $item->getVideo(),
                'url' => $item->getUrl(),
                'type' => $item->getType(),
                'nom_auteur' => $item->getNomAuteur(),
                'mention' => $item->getMention() ? $item->getMention()->getName() : null,
                'parcours' => $item->getParcours() ? $item->getParcours()->getName() : null,
                'niveau' => $item->getNiveau() ? $item->getNiveau()->getNom() : null,
            ];

            // Ajoutez l'ID de l'examen si l'item est lié à un examen
            if ($item->getType() === 'EXAMEN' && $item->getExamens()->count() > 0) {
                $data['examenId'] = $item->getExamens()->first()->getId();
            }

            return $data;
        }, $items);
    }
}