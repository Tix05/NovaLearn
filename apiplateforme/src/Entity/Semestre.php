<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"name": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"semestre:read"}},
 *     denormalizationContext={"groups"={"semestre:write"}},
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
 *     "name": "partial",
 *     "code": "exact",
 *     "ues.nom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "code", "created_at"})
 * @ORM\Entity(repositoryClass=SemestreRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Semestre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"semestre:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"semestre:read", "semestre:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"semestre:read", "semestre:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Regex(
     *     pattern="/^[A-Z0-9]+$/",
     *     message="Le code doit contenir uniquement des majuscules et des chiffres"
     * )
     */
    private $code;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"semestre:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"semestre:read"})
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=Ue::class, mappedBy="semestre")
     * @Groups({"semestre:read"})
     */
    private $ues;

    public function __construct()
    {
        $this->ues = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    /**
     * @return Collection<int, Ue>
     */
    public function getUes(): Collection
    {
        return $this->ues;
    }

    public function addUe(Ue $ue): self
    {
        if (!$this->ues->contains($ue)) {
            $this->ues[] = $ue;
            $ue->setSemestre($this);
        }
        return $this;
    }

    public function removeUe(Ue $ue): self
    {
        if ($this->ues->removeElement($ue)) {
            if ($ue->getSemestre() === $this) {
                $ue->setSemestre(null);
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
        return $this->name;
    }
}