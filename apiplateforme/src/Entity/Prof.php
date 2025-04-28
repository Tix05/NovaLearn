<?php

namespace App\Entity;

use App\Repository\ProfRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"user.nom": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"prof:read"}},
 *     denormalizationContext={"groups"={"prof:write"}},
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
 *     "user.nom": "partial",
 *     "user.prenom": "partial",
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "user.nom", "created_at"})
 * @ApiFilter(BooleanFilter::class, properties={"status"})
 * @ORM\Entity(repositoryClass=ProfRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Prof
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"prof:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"prof:read", "prof:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"prof:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"prof:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="profs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"prof:read", "prof:write"})
     * @Assert\NotNull
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Ec::class, mappedBy="prof")
     * @Groups({"prof:read", "prof:write"})
     */
    private $ecs;

    public function __construct()
    {
        $this->ecs = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeImmutable $updated_at): self
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
            $ec->setProf($this);
        }

        return $this;
    }

    public function removeEc(Ec $ec): self
    {
        if ($this->ecs->removeElement($ec)) {
            // set the owning side to null (unless already changed)
            if ($ec->getProf() === $this) {
                $ec->setProf(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->user ? $this->user->getNomComplet() : 'Nouveau Prof';
    }
}