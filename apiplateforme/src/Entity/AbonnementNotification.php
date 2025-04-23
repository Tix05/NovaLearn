<?php

namespace App\Entity;

use App\Repository\AbonnementNotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"createdAt": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"abonnement:read"}},
 *     denormalizationContext={"groups"={"abonnement:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_USER') and object.getUser() == user"},
 *         "patch"={"security"="is_granted('ROLE_USER') and object.getUser() == user"},
 *         "delete"={"security"="is_granted('ROLE_USER') and object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "user.id": "exact",
 *     "typeNotification": "exact",
 *     "canal": "exact"
 * })
 * @ApiFilter(BooleanFilter::class, properties={"actif"})
 * @ORM\Entity(repositoryClass=AbonnementNotificationRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="user_type_canal_unique",
 *             columns={"user_id", "type_notification", "canal"}
 *         )
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class AbonnementNotification
{
    public const CANAL_EMAIL = 'EMAIL';
    public const CANAL_SMS = 'SMS';
    public const CANAL_APPLICATION = 'APPLICATION';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"abonnement:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="abonnementNotifications")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"abonnement:read", "abonnement:write"})
     * @Assert\NotNull
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"abonnement:read", "abonnement:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $typeNotification = null;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"abonnement:read", "abonnement:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     AbonnementNotification::CANAL_EMAIL,
     *     AbonnementNotification::CANAL_SMS,
     *     AbonnementNotification::CANAL_APPLICATION
     * })
     */
    private ?string $canal = null;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"abonnement:read", "abonnement:write"})
     */
    private ?bool $actif = true;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"abonnement:read"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"abonnement:read"})
     */
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTypeNotification(): ?string
    {
        return $this->typeNotification;
    }

    public function setTypeNotification(string $typeNotification): static
    {
        $this->typeNotification = $typeNotification;
        return $this;
    }

    public function getCanal(): ?string
    {
        return $this->canal;
    }

    public function setCanal(string $canal): static
    {
        $this->canal = $canal;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    public static function getCanals(): array
    {
        return [
            self::CANAL_EMAIL,
            self::CANAL_SMS,
            self::CANAL_APPLICATION
        ];
    }
    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}