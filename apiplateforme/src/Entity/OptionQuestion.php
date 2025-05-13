<?php

namespace App\Entity;

use App\Repository\OptionQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"option:read"}},
 *     denormalizationContext={"groups"={"option:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_PROFESSEUR')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_PROFESSEUR') and object.getQuestion().getExamen().getAuteur() == user"},
 *         "patch"={"security"="is_granted('ROLE_PROFESSEUR') and object.getQuestion().getExamen().getAuteur() == user"},
 *         "delete"={"security"="is_granted('ROLE_PROFESSEUR') and object.getQuestion().getExamen().getAuteur() == user"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=OptionQuestionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class OptionQuestion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"option:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"option:read", "option:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $texte = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"option:read", "option:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $valeur = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"option:read"})
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"option:read"})
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity=Question::class, inversedBy="options")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"option:read", "option:write"})
     * @Assert\NotNull
     */
    private ?Question $question = null;

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

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;
        return $this;
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

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }
}