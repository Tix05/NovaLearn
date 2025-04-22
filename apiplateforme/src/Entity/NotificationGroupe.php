<?php

namespace App\Entity;

use App\Repository\NotificationGroupeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"createdAt": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"notification_groupe:read"}},
 *     denormalizationContext={"groups"={"notification_groupe:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "notification.id": "exact",
 *     "parcours.id": "exact",
 *     "niveau.id": "exact",
 *     "mention.id": "exact",
 *     "ec.id": "exact"
 * })
 * @ORM\Entity(repositoryClass=NotificationGroupeRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_notification", columns={"notification_id"}),
 *     @ORM\Index(name="idx_parcours", columns={"parcours_id"}),
 *     @ORM\Index(name="idx_niveau", columns={"niveau_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class NotificationGroupe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"notification_groupe:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Notification::class, inversedBy="notificationGroupes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"notification_groupe:read", "notification_groupe:write"})
     * @Assert\NotNull
     */
    private ?Notification $notification = null;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="notificationGroupes")
     * @Groups({"notification_groupe:read", "notification_groupe:write"})
     */
    private ?Parcours $parcours = null;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="notificationGroupes")
     * @Groups({"notification_groupe:read", "notification_groupe:write"})
     */
    private ?Niveau $niveau = null;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="notificationGroupes")
     * @Groups({"notification_groupe:read", "notification_groupe:write"})
     */
    private ?Mention $mention = null;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class, inversedBy="notificationGroupes")
     * @Groups({"notification_groupe:read", "notification_groupe:write"})
     */
    private ?Ec $ec = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"notification_groupe:read"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): static
    {
        $this->notification = $notification;
        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;
        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): static
    {
        $this->mention = $mention;
        return $this;
    }

    public function getEc(): ?Ec
    {
        return $this->ec;
    }

    public function setEc(?Ec $ec): static
    {
        $this->ec = $ec;
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

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}