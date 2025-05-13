<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"question:read"}},
 *     denormalizationContext={"groups"={"question:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_PROFESSEUR')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_PROFESSEUR') and object.getExamen().getAuteur() == user"},
 *         "patch"={"security"="is_granted('ROLE_PROFESSEUR') and object.getExamen().getAuteur() == user"},
 *         "delete"={"security"="is_granted('ROLE_PROFESSEUR') and object.getExamen().getAuteur() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "examen.id": "exact",
 *     "type": "exact"
 * })
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Question
{
    public const TYPE_RADIO = 'radio';
    public const TYPE_ESSAY = 'essay';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"question:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="text")
     * @Groups({"question:read", "question:write"})
     * @Assert\NotBlank
     */
    private ?string $texte = null;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"question:read", "question:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     Question::TYPE_RADIO,
     *     Question::TYPE_ESSAY
     * })
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"question:read", "question:write"})
     */
    private ?string $reponseCorrecte = null;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"question:read", "question:write"})
     * @Assert\PositiveOrZero
     */
    private ?int $points = 0;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"question:read"})
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"question:read"})
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Examen::class, inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"question:read", "question:write"})
     * @Assert\NotNull
     */
    private ?Examen $examen = null;

    /**
     * @ORM\OneToMany(targetEntity=OptionQuestion::class, mappedBy="question", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"question:read"})
     */
    private Collection $options;

    /**
     * @ORM\OneToMany(targetEntity=ReponseEtudiant::class, mappedBy="question")
     */
    private Collection $reponsesEtudiants;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->options = new ArrayCollection();
        $this->reponsesEtudiants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;
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

    public function getReponseCorrecte(): ?string
    {
        return $this->reponseCorrecte;
    }

    public function setReponseCorrecte(?string $reponseCorrecte): self
    {
        $this->reponseCorrecte = $reponseCorrecte;
        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
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

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(OptionQuestion $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setQuestion($this);
        }
        return $this;
    }

    public function removeOption(OptionQuestion $option): self
    {
        if ($this->options->removeElement($option)) {
            if ($option->getQuestion() === $this) {
                $option->setQuestion(null);
            }
        }
        return $this;
    }

    public function getReponsesEtudiants(): Collection
    {
        return $this->reponsesEtudiants;
    }

    public function addReponseEtudiant(ReponseEtudiant $reponseEtudiant): self
    {
        if (!$this->reponsesEtudiants->contains($reponseEtudiant)) {
            $this->reponsesEtudiants[] = $reponseEtudiant;
            $reponseEtudiant->setQuestion($this);
        }
        return $this;
    }

    public function removeReponseEtudiant(ReponseEtudiant $reponseEtudiant): self
    {
        if ($this->reponsesEtudiants->removeElement($reponseEtudiant)) {
            if ($reponseEtudiant->getQuestion() === $this) {
                $reponseEtudiant->setQuestion(null);
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

    public static function getTypes(): array
    {
        return [
            self::TYPE_RADIO,
            self::TYPE_ESSAY
        ];
    }
}