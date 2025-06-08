<?php

namespace App\Entity;

use App\Repository\CorrectionExamenRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"correction:read"}},
 *     denormalizationContext={"groups"={"correction:write"}},
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER')"},
 *         "post"={"security"="is_granted('ROLE_PROFESSEUR')"}
 *     },
 *     itemOperations={
 *         "get"={"security"="is_granted('ROLE_USER') and (object.getEtudiant().getUser() == user or object.getExamen().getAuteur() == user)"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "examen.id": "exact",
 *     "etudiant.id": "exact"
 * })
 * @ORM\Entity(repositoryClass=CorrectionExamenRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CorrectionExamen
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"correction:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="float")
     * @Groups({"correction:read"})
     * @Assert\Range(min=0, max=20)
     */
    private ?float $noteTotale = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"correction:read"})
     */
    private ?string $fichierRapport = null;

    /**
     * @Vich\UploadableField(mapping="correction_file", fileNameProperty="fichierRapport")
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf"}
     * )
     */
    private ?File $rapportFile = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"correction:read"})
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"correction:read"})
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Examen::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"correction:read"})
     * @Assert\NotNull
     */
    private ?Examen $examen = null;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"correction:read"})
     * @Assert\NotNull
     */
    private ?Etudiant $etudiant = null;

    /**
     * @ORM\OneToMany(targetEntity=ReponseEtudiant::class, mappedBy="correctionExamen", cascade={"persist", "remove"})
     * @Groups({"correction:read"})
     */
    private Collection $reponses;

    /**
     * @Groups({"correction:read"})
     */
    public function getNomEc(): ?string
    {
        return $this->examen && $this->examen->getEc() ? $this->examen->getEc()->getName() : null;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->reponses = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoteTotale(): ?float
    {
        return $this->noteTotale;
    }

    public function setNoteTotale(float $noteTotale): self
    {
        $this->noteTotale = $noteTotale;
        return $this;
    }

    public function getFichierRapport(): ?string
    {
        return $this->fichierRapport;
    }

    public function setFichierRapport(?string $fichierRapport): self
    {
        $this->fichierRapport = $fichierRapport;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getExamen(): ?Examen
    {
        return $this->examen;
    }

    public function setExamen(?Examen $examen): self
    {
        $this->examen = $examen;
        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;
        return $this;
    }

    /**
     * @return Collection|ReponseEtudiant[]
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(ReponseEtudiant $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setCorrectionExamen($this);
        }
        return $this;
    }

    public function removeReponse(ReponseEtudiant $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getCorrectionExamen() === $this) {
                $reponse->setCorrectionExamen(null);
            }
        }
        return $this;
    }

     public function setRapportFile(?File $rapportFile = null): void
    {
        $this->rapportFile = $rapportFile;

        if (null !== $rapportFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getRapportFile(): ?File
    {
        return $this->rapportFile;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }
}