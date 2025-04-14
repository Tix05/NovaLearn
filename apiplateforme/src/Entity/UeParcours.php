<?php

namespace App\Entity;

use App\Repository\UeParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UeParcoursRepository::class)]
class UeParcours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $ue_id;

    #[ORM\Column(type: 'integer')]
    private $parcours_id;

    #[ORM\Column(type: 'integer')]
    private $niveau_id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUeId(): ?int
    {
        return $this->ue_id;
    }

    public function setUeId(?int $ue_id): self
    {
        $this->ue_id = $ue_id;

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
