<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"matricule": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"etudiant:read"}},
 *     denormalizationContext={"groups"={"etudiant:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN')"},
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "matricule": "exact",
 *     "user.nom": "partial",
 *     "user.prenom": "partial",
 *     "mention.nom": "partial",
 *     "parcours.nom": "partial",
 *     "niveau.nom": "partial",
 *     "year.annee": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={
 *     "id", "matricule", "date_inscription", "created_at"
 * })
 * @ApiFilter(DateFilter::class, properties={"date_inscription"})
 * @ApiFilter(BooleanFilter::class, properties={"status"})
 * @ORM\Entity(repositoryClass=EtudiantRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Etudiant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"etudiant:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $matricule;

    /**
     * @ORM\Column(type="date")
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotBlank
     * @Assert\Type("\DateTimeInterface")
     */
    private $date_inscription;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"etudiant:read", "etudiant:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\Length(max=255)
     */
    private $fichier_virement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\Length(max=255)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotBlank
     * @Assert\Choice({"virement", "espece", "cheque", "bourse"})
     * @Assert\Length(max=255)
     */
    private $type_payement;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"etudiant:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"etudiant:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="etudiants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant:read"})
     * @Assert\NotNull
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="etudiants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotNull
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="etudiants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotNull
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="etudiants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotNull
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Years::class, inversedBy="etudiants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant:read", "etudiant:write"})
     * @Assert\NotNull
     */
    private $year;

    /**
     * @ORM\OneToMany(targetEntity=ReponseEtudiant::class, mappedBy="etudiant")
     */
    private Collection $reponsesExamens;

    /**
     * @ORM\OneToMany(targetEntity=CorrectionExamen::class, mappedBy="etudiant")
     */
    private Collection $correctionsExamens;

    /**
     * @ORM\OneToMany(targetEntity=EtudiantExamenStatut::class, mappedBy="etudiant", orphanRemoval=true)
     */
    private $etudiantExamenStatuts;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->reponsesExamens = new ArrayCollection();
        $this->correctionsExamens = new ArrayCollection();
        $this->etudiantExamenStatuts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getYear(): ?Years
    {
        return $this->year;
    }

    public function setYear(?Years $year): self
    {
        $this->year = $year;
        return $this;
    }

    public function getReponsesExamens(): Collection
    {
        return $this->reponsesExamens;
    }

    public function addReponsesExamen(ReponseEtudiant $reponsesExamen): self
    {
        if (!$this->reponsesExamens->contains($reponsesExamen)) {
            $this->reponsesExamens[] = $reponsesExamen;
            $reponsesExamen->setEtudiant($this);
        }
        return $this;
    }

    public function removeReponsesExamen(ReponseEtudiant $reponsesExamen): self
    {
        if ($this->reponsesExamens->removeElement($reponsesExamen)) {
            if ($reponsesExamen->getEtudiant() === $this) {
                $reponsesExamen->setEtudiant(null);
            }
        }
        return $this;
    }

    public function getCorrectionsExamens(): Collection
    {
        return $this->correctionsExamens;
    }

    public function addCorrectionsExamen(CorrectionExamen $correctionsExamen): self
    {
        if (!$this->correctionsExamens->contains($correctionsExamen)) {
            $this->correctionsExamens[] = $correctionsExamen;
            $correctionsExamen->setEtudiant($this);
        }
        return $this;
    }

    public function removeCorrectionsExamen(CorrectionExamen $correctionsExamen): self
    {
        if ($this->correctionsExamens->removeElement($correctionsExamen)) {
            if ($correctionsExamen->getEtudiant() === $this) {
                $correctionsExamen->setEtudiant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|EtudiantExamenStatut[]
     */
    public function getEtudiantExamenStatuts(): Collection
    {
        return $this->etudiantExamenStatuts;
    }

    public function addEtudiantExamenStatut(EtudiantExamenStatut $etudiantExamenStatut): self
    {
        if (!$this->etudiantExamenStatuts->contains($etudiantExamenStatut)) {
            $this->etudiantExamenStatuts[] = $etudiantExamenStatut;
            $etudiantExamenStatut->setEtudiant($this);
        }

        return $this;
    }

    public function removeEtudiantExamenStatut(EtudiantExamenStatut $etudiantExamenStatut): self
    {
        if ($this->etudiantExamenStatuts->removeElement($etudiantExamenStatut)) {
            // set the owning side to null (unless already changed)
            if ($etudiantExamenStatut->getEtudiant() === $this) {
                $etudiantExamenStatut->setEtudiant(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}