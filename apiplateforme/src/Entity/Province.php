<?php

namespace App\Entity;

use App\Repository\ProvinceRepository;
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
 *         "order"={"region": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"province:read"}},
 *     denormalizationContext={"groups"={"province:write"}},
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
 *     "region": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "region"})
 * @ORM\Entity(repositoryClass=ProvinceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Province
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"province:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"province:read", "province:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $region;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"province:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"province:read"})
     */
    private $updated_at;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;
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
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->region;
    }
}