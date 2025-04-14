<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
class Etudiant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $user_id;

    #[ORM\Column(type: 'integer')]
    private $mention_id;

    #[ORM\Column(type: 'integer')]
    private $year_id;

    #[ORM\Column(type: 'integer')]
    private $parcours_id;

    #[ORM\Column(type: 'integer')]
    private $niveau_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $matricule;

    #[ORM\Column(type: 'date')]
    private $date_inscription;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $fichier_virement;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $reference;

    #[ORM\Column(type: 'string', length: 255)]
    private $type_payement;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMentionId(): ?int
    {
        return $this->mention_id;
    }

    public function setMentionId(int $mention_id): self
    {
        $this->mention_id = $mention_id;

        return $this;
    }

    public function getYearId(): ?int
    {
        return $this->year_id;
    }

    public function setYearId(int $year_id): self
    {
        $this->year_id = $year_id;

        return $this;
    }

    public function getParcoursId(): ?int
    {
        return $this->parcours_id;
    }

    public function setParcoursId(int $parcours_id): self
    {
        $this->parcours_id = $parcours_id;

        return $this;
    }

    public function getNiveauId(): ?int
    {
        return $this->niveau_id;
    }

    public function setNiveauId(int $niveau_id): self
    {
        $this->niveau_id = $niveau_id;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFichierVirement(): ?string
    {
        return $this->fichier_virement;
    }

    public function setFichierVirement(?string $fichier_virement): self
    {
        $this->fichier_virement = $fichier_virement;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTypePayement(): ?string
    {
        return $this->type_payement;
    }

    public function setTypePayement(string $type_payement): self
    {
        $this->type_payement = $type_payement;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
