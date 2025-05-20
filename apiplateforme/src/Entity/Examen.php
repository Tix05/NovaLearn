<?php

namespace App\Entity;

use App\Repository\ExamenRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiFilter;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"createdAt": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"examen:read"}},
 *     denormalizationContext={"groups"={"examen:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_PROFESSEUR')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_PROFESSEUR') and object.getAuteur() == user"},
 *         "patch"={"security"="is_granted('ROLE_PROFESSEUR') and object.getAuteur() == user"},
 *         "delete"={"security"="is_granted('ROLE_PROFESSEUR') and object.getAuteur() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "titre": "partial",
 *     "statut": "exact",
 *     "type": "exact",
 *     "ec.id": "exact",
 *     "auteur.id": "exact"
 * })
 * @ApiFilter(DateFilter::class, properties={"date_creation", "date_publication", "date_expiration"})
 * @ORM\Entity(repositoryClass=ExamenRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Examen
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_PUBLIE = 'publie';
    public const STATUT_TERMINE = 'termine';

    public const TYPE_PDF = 'pdf';
    public const TYPE_IA_GENERE = 'ia_genere';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"examen:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"examen:read", "examen:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $titre = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"examen:read", "examen:write"})
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"examen:read", "examen:write"})
     */
    private ?string $fichier = null;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"examen:read", "examen:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     Examen::STATUT_BROUILLON,
     *     Examen::STATUT_EN_ATTENTE,
     *     Examen::STATUT_PUBLIE,
     *     Examen::STATUT_TERMINE
     * })
     */
    private ?string $statut = self::STATUT_BROUILLON;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"examen:read", "examen:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     Examen::TYPE_PDF,
     *     Examen::TYPE_IA_GENERE
     * })
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"examen:read", "examen:write"})
     * @Assert\Positive
     */
    private ?int $duree = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"examen:read"})
     */
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"examen:read", "examen:write"})
     */
    private ?\DateTimeInterface $datePublication = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"examen:read", "examen:write"})
     */
    private ?\DateTimeInterface $dateExpiration = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"examen:read"})
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"examen:read"})
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"examen:read", "examen:write"})
     * @Assert\NotNull
     */
    private ?Ec $ec = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"examen:read"})
     * @Assert\NotNull
     */
    private ?User $auteur = null;

    /**
     * @ORM\ManyToOne(targetEntity=Agenda::class)
     * @Groups({"examen:read", "examen:write"})
     */
    private ?Agenda $agenda = null;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="examen", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"examen:read"})
     */
    private Collection $questions;

    /**
     * @ORM\OneToMany(targetEntity=ReponseEtudiant::class, mappedBy="examen")
     */
    private Collection $reponsesEtudiants;

    /**
     * @ORM\OneToMany(targetEntity=CorrectionExamen::class, mappedBy="examen")
     */
    private Collection $corrections;

    /**
     * @ORM\OneToMany(targetEntity=EtudiantExamenStatut::class, mappedBy="examen", orphanRemoval=true)
     */
    private $etudiantExamenStatuts;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->questions = new ArrayCollection();
        $this->reponsesEtudiants = new ArrayCollection();
        $this->corrections = new ArrayCollection();
        $this->etudiantExamenStatuts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): self
    {
        $this->fichier = $fichier;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(?\DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
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

    public function getEc(): ?Ec
    {
        return $this->ec;
    }

    public function setEc(?Ec $ec): self
    {
        $this->ec = $ec;
        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;
        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setExamen($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getExamen() === $this) {
                $question->setExamen(null);
            }
        }
        return $this;
    }

    // Méthodes pour les réponses étudiant
    public function getReponsesEtudiants(): Collection
    {
        return $this->reponsesEtudiants;
    }

    public function addReponseEtudiant(ReponseEtudiant $reponseEtudiant): self
    {
        if (!$this->reponsesEtudiants->contains($reponseEtudiant)) {
            $this->reponsesEtudiants[] = $reponseEtudiant;
            $reponseEtudiant->setExamen($this);
        }
        return $this;
    }

    public function removeReponseEtudiant(ReponseEtudiant $reponseEtudiant): self
    {
        if ($this->reponsesEtudiants->removeElement($reponseEtudiant)) {
            if ($reponseEtudiant->getExamen() === $this) {
                $reponseEtudiant->setExamen(null);
            }
        }
        return $this;
    }

    public function getCorrections(): Collection
    {
        return $this->corrections;
    }

    public function addCorrection(CorrectionExamen $correction): self
    {
        if (!$this->corrections->contains($correction)) {
            $this->corrections[] = $correction;
            $correction->setExamen($this);
        }
        return $this;
    }

    public function removeCorrection(CorrectionExamen $correction): self
    {
        if ($this->corrections->removeElement($correction)) {
            if ($correction->getExamen() === $this) {
                $correction->setExamen(null);
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
            $etudiantExamenStatut->setExamen($this);
        }

        return $this;
    }

    public function removeEtudiantExamenStatut(EtudiantExamenStatut $etudiantExamenStatut): self
    {
        if ($this->etudiantExamenStatuts->removeElement($etudiantExamenStatut)) {
            // set the owning side to null (unless already changed)
            if ($etudiantExamenStatut->getExamen() === $this) {
                $etudiantExamenStatut->setExamen(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_BROUILLON,
            self::STATUT_EN_ATTENTE,
            self::STATUT_PUBLIE,
            self::STATUT_TERMINE
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PDF,
            self::TYPE_IA_GENERE
        ];
    }
}