<?php

namespace App\Entity;

use App\Repository\BibliothequeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()   
 * @ORM\Entity(repositoryClass="App\Repository\BibliothequeRepository")
 */

class Bibliotheque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $mention_id;

    #[ORM\Column(type: 'integer')]
    private $ec_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $titre;

    #[ORM\Column(type: 'string', length: 255)]
    private $fichier;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $parcours_id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    #[ORM\ManyToOne(targetEntity: Mention::class, inversedBy: 'bibliotheques')]
    private $mention;

    #[ORM\ManyToOne(targetEntity: Parcours::class, inversedBy: 'bibliotheques')]
    private $parcours;

    #[ORM\ManyToOne(targetEntity: Ec::class, inversedBy: 'bibliotheques')]
    private $ec;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMentionId(): ?int
    {
        return $this->mention_id;
    }

    public function setMentionId(?int $mention_id): self
    {
        $this->mention_id = $mention_id;

        return $this;
    }

    public function getEcId(): ?int
    {
        return $this->ec_id;
    }

    public function setEcId(int $ec_id): self
    {
        $this->ec_id = $ec_id;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): self
    {
        $this->fichier = $fichier;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getParcoursId(): ?int
    {
        return $this->parcours_id;
    }

    public function setParcoursId(?int $parcours_id): self
    {
        $this->parcours_id = $parcours_id;

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

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getEc(): ?Ec
    {
        return $this->ec;
    }

    public function setEc(?Ec $ec): self
    {
        $this->ec = $ec;

        return $this;
    }
}
