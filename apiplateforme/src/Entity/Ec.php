<?php

namespace App\Entity;

use App\Repository\EcRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()   
 * @ORM\Entity(repositoryClass="App\Repository\EcRepository")
 */
class Ec
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $ue_id;

    #[ORM\Column(type: 'integer')]
    private $prof_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $code;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $coeff;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Prof::class)]
    private $profs;

    #[ORM\ManyToOne(targetEntity: Ue::class, inversedBy: 'ecs')]
    private $ue;

    #[ORM\ManyToOne(targetEntity: Prof::class, inversedBy: 'ecs')]
    private $prof;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Bibliotheque::class)]
    private $bibliotheques;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Commentaire::class)]
    private $commentaires;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: FichierSupport::class)]
    private $fichierSupports;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->bibliotheques = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->fichierSupports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUeId(): ?int
    {
        return $this->ue_id;
    }

    public function setUeId(int $ue_id): self
    {
        $this->ue_id = $ue_id;

        return $this;
    }

    public function getProfId(): ?int
    {
        return $this->prof_id;
    }

    public function setProfId(int $prof_id): self
    {
        $this->prof_id = $prof_id;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getCoeff(): ?int
    {
        return $this->coeff;
    }

    public function setCoeff(int $coeff): self
    {
        $this->coeff = $coeff;

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

    /**
     * @return Collection<int, Prof>
     */
    public function getProfs(): Collection
    {
        return $this->profs;
    }

    public function addProf(Prof $prof): self
    {
        if (!$this->profs->contains($prof)) {
            $this->profs[] = $prof;
            $prof->setEc($this);
        }

        return $this;
    }

    public function removeProf(Prof $prof): self
    {
        if ($this->profs->removeElement($prof)) {
            // set the owning side to null (unless already changed)
            if ($prof->getEc() === $this) {
                $prof->setEc(null);
            }
        }

        return $this;
    }

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;

        return $this;
    }

    public function getProf(): ?Prof
    {
        return $this->prof;
    }

    public function setProf(?Prof $prof): self
    {
        $this->prof = $prof;

        return $this;
    }

    /**
     * @return Collection<int, Bibliotheque>
     */
    public function getBibliotheques(): Collection
    {
        return $this->bibliotheques;
    }

    public function addBibliotheque(Bibliotheque $bibliotheque): self
    {
        if (!$this->bibliotheques->contains($bibliotheque)) {
            $this->bibliotheques[] = $bibliotheque;
            $bibliotheque->setEc($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): self
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getEc() === $this) {
                $bibliotheque->setEc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setEc($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getEc() === $this) {
                $commentaire->setEc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FichierSupport>
     */
    public function getFichierSupports(): Collection
    {
        return $this->fichierSupports;
    }

    public function addFichierSupport(FichierSupport $fichierSupport): self
    {
        if (!$this->fichierSupports->contains($fichierSupport)) {
            $this->fichierSupports[] = $fichierSupport;
            $fichierSupport->setEc($this);
        }

        return $this;
    }

    public function removeFichierSupport(FichierSupport $fichierSupport): self
    {
        if ($this->fichierSupports->removeElement($fichierSupport)) {
            // set the owning side to null (unless already changed)
            if ($fichierSupport->getEc() === $this) {
                $fichierSupport->setEc(null);
            }
        }

        return $this;
    }
}
