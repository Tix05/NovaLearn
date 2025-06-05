<?php

namespace App\Entity;

use App\Repository\UeRepository;
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
 *     normalizationContext={"groups"={"ue:read"}},
 *     denormalizationContext={"groups"={"ue:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "controller"=UeController::class,
 *             "deserialize"=false
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "controller"=UeController::class,
 *             "deserialize"=false
 *         },
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 *     "code": "exact",
 *     "mention.name": "partial",
 *     "semestre.libelle": "partial",
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "code", "created_at"})
 * @ORM\Entity(repositoryClass=UeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="ue_name_idx", columns={"name"}),
 *     @ORM\Index(name="ue_code_idx", columns={"code"})
 * })
 */
class Ue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"ue:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"ue:read", "ue:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"ue:read", "ue:write"})
     * @Assert\Length(max=255)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"ue:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"ue:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="ues")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ue:read", "ue:write"})
     * @Assert\NotNull
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="ues")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ue:read", "ue:write"})
     * @Assert\NotNull
     */
    private $semestre;

    /**
     * @ORM\OneToMany(targetEntity=Ec::class, mappedBy="ue")
     * @Groups({"ue:read"})
     */
    private $ecs;

    /**
     * @ORM\OneToMany(targetEntity=UeParcours::class, mappedBy="ue", cascade={"persist", "remove"})
     * @Groups({"ue:read", "ue:write"})
     */
    private $ueParcours;

    public function __construct()
    {
        $this->ecs = new ArrayCollection();
        $this->ueParcours = new ArrayCollection();
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

    public function setCode(?string $code): self
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

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;
        return $this;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;
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
            $ec->setUe($this);
        }
        return $this;
    }

    public function removeEc(Ec $ec): self
    {
        if ($this->ecs->removeElement($ec)) {
            if ($ec->getUe() === $this) {
                $ec->setUe(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|UeParcours[]
     */
    public function getUeParcours(): Collection
    {
        return $this->ueParcours;
    }

    public function addUeParcours(UeParcours $ueParcours): self
    {
        if (!$this->ueParcours->contains($ueParcours)) {
            $this->ueParcours[] = $ueParcours;
            $ueParcours->setUe($this);
        }
        return $this;
    }

    public function removeUeParcours(UeParcours $ueParcours): self
    {
        if ($this->ueParcours->removeElement($ueParcours)) {
            if ($ueParcours->getUe() === $this) {
                $ueParcours->setUe(null);
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