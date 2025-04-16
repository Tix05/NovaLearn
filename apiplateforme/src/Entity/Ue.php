<?php

namespace App\Entity;

use App\Repository\UeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()   
 * @ORM\Entity(repositoryClass="App\Repository\UeRepository")
 */
class Ue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $mention_id;

    #[ORM\Column(type: 'integer')]
    private $semestre_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $niveau_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $code;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    #[ORM\ManyToOne(targetEntity: Mention::class, inversedBy: 'ues')]
    private $mention;

    #[ORM\ManyToOne(targetEntity: Semestre::class, inversedBy: 'ues')]
    private $semestre;

    #[ORM\ManyToOne(targetEntity: Niveau::class, inversedBy: 'ues')]
    private $niveau;

    #[ORM\OneToMany(mappedBy: 'ue', targetEntity: Ec::class)]
    private $ecs;

    public function __construct()
    {
        $this->ecs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSemestreId(): ?int
    {
        return $this->semestre_id;
    }

    public function setSemestreId(int $semestre_id): self
    {
        $this->semestre_id = $semestre_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNiveauId(): ?int
    {
        return $this->niveau_id;
    }

    public function setNiveauId(?int $niveau_id): self
    {
        $this->niveau_id = $niveau_id;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * @return Collection<int, Ec>
     */
    public function getEcs(): Collection
    {
        return $this->ecs;
    }

    public function addEc(Ec $ec): self
    {
        if (!$this->ecs->contains($ec)) {
            $this->ecs[] = $ec;
            $ec->setUe($this);
        }

        return $this;
    }

    public function removeEc(Ec $ec): self
    {
        if ($this->ecs->removeElement($ec)) {
            // set the owning side to null (unless already changed)
            if ($ec->getUe() === $this) {
                $ec->setUe(null);
            }
        }

        return $this;
    }
}
