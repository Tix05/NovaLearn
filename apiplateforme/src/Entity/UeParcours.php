<?php

namespace App\Entity;

use App\Repository\UeParcoursRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"ue_parcours:read"}},
 *     denormalizationContext={"groups"={"ue_parcours:write"}},
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
 *     "ue.id": "exact",
 *     "parcours.id": "exact",
 * })
 * @ApiFilter(OrderFilter::class, properties={"created_at"})
 * @ORM\Entity(repositoryClass=UeParcoursRepository::class)
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ue_parcours_unique", columns={"ue_id", "parcours_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class UeParcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"ue_parcours:read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ue", inversedBy="ueParcours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ue;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parcours")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ue_parcours:read", "ue_parcours:write"})
     * @Assert\NotNull
     */
    private $parcours;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"ue_parcours:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"ue_parcours:read"})
     */
    private $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;
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

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}