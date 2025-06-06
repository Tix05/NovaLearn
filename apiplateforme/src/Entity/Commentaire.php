<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"time": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"commentaire:read"}},
 *     denormalizationContext={"groups"={"commentaire:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "security"="is_granted('ROLE_USER')",
 *             "validation_groups"={"Default", "commentaire:create"}
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"},
 *         "patch"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "contenu": "partial",
 *     "user.id": "exact",
 *     "ec.id": "exact",
 *     "user.nom": "partial",
 *     "user.prenom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "time", "createdAt"})
 * @ApiFilter(DateFilter::class, properties={"time", "createdAt"})
 * @ApiFilter(BooleanFilter::class, properties={"status"})
 * @ORM\Entity(repositoryClass=CommentaireRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Commentaire
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"commentaire:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"commentaire:read", "commentaire:write"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=5000,
     *     minMessage="Le commentaire doit contenir au moins {{ limit }} caractères",
     *     maxMessage="Le commentaire ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    private $contenu;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"commentaire:read"})
     */
    private $time;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"commentaire:read", "commentaire:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"commentaire:read"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commentaires")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"commentaire:read"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class, inversedBy="commentaires")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"commentaire:read", "commentaire:write"})
     * @Assert\NotNull
     */
    private $ec;

    /**
     * @ORM\ManyToOne(targetEntity=Commentaire::class, inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @Groups({"commentaire:read", "commentaire:write"})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="parent")
     * @Groups({"commentaire:read"})
     */
    private $children;

    public function __construct()
    {
        $this->time = new \DateTime();
        $this->createdAt = new \DateTimeImmutable();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;
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
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    public function getEc(): ?Ec
    {
        return $this->ec;
    }

    public function setEc(?Ec $ec): self
    {
        $this->ec = $ec;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestamps(): void
    {
        if ($this->getTime() === null) {
            $this->time = new \DateTime();
        }
    }
}