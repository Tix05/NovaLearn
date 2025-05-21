<?php

namespace App\Entity;

use App\Repository\ReponseEtudiantRepository;
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
 *     normalizationContext={"groups"={"reponse:read"}},
 *     denormalizationContext={"groups"={"reponse:write"}},
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER')"},
 *         "post"={"security"="is_granted('ROLE_ETUDIANT')"}
 *     },
 *     itemOperations={
 *         "get"={"security"="is_granted('ROLE_USER') and (object.getEtudiant().getUser() == user or object.getQuestion().getExamen().getAuteur() == user)"},
 *         "put"={"security"="is_granted('ROLE_ETUDIANT') and object.getEtudiant().getUser() == user"},
 *         "patch"={"security"="is_granted('ROLE_ETUDIANT') and object.getEtudiant().getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "question.id": "exact",
 *     "etudiant.id": "exact",
 *     "examen.id": "exact"
 * })
 * @ORM\Entity(repositoryClass=ReponseEtudiantRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ReponseEtudiant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"reponse:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="text")
     * @Groups({"reponse:read", "reponse:write"})
     * @Assert\NotBlank
     */
    private ?string $valeur = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"reponse:read"})
     */
    private ?float $note = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"reponse:read"})
     */
    private ?string $commentaire = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reponse:read"})
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reponse:read"})
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Question::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reponse:read", "reponse:write"})
     * @Assert\NotNull
     */
    private ?Question $question = null;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reponse:read"})
     * @Assert\NotNull
     */
    private ?Etudiant $etudiant = null;

    /**
     * @ORM\ManyToOne(targetEntity=Examen::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reponse:read", "reponse:write"})
     * @Assert\NotNull
     */
    private ?Examen $examen = null;

    /**
     * @ORM\ManyToOne(targetEntity="CorrectionExamen", inversedBy="reponses")
     * @ORM\JoinColumn(name="correction_examen_id", referencedColumnName="id")
     */
    private $correctionExamen;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): self
    {
        $this->valeur = $valeur;
        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
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

    public function getExamen(): ?Examen
    {
        return $this->examen;
    }

    public function setExamen(?Examen $examen): self
    {
        $this->examen = $examen;
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }
}