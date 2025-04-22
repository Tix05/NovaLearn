<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
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
 *         "order"={"dateEnvoi": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"message:read"}},
 *     denormalizationContext={"groups"={"message:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Seuls les utilisateurs connectés peuvent envoyer des messages"
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getExpediteur() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getExpediteur() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "contenu": "partial",
 *     "conversation.id": "exact",
 *     "expediteur.id": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "dateEnvoi"})
 * @ApiFilter(DateFilter::class, properties={"dateEnvoi"})
 * @ApiFilter(BooleanFilter::class, properties={"lu"})
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_conversation_date", columns={"conversation_id", "date_envoi"})
 * })
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"message:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"message:read", "message:write"})
     * @Assert\NotNull
     */
    private ?Conversation $conversation = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"message:read", "message:write"})
     * @Assert\NotNull
     */
    private ?User $expediteur = null;

    /**
     * @ORM\Column(type="text")
     * @Groups({"message:read", "message:write"})
     * @Assert\NotBlank
     * @Assert\Length(min=1, max=5000)
     */
    private ?string $contenu = null;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"message:read", "message:write"})
     */
    private bool $lu = false;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"message:read"})
     */
    private ?\DateTimeInterface $dateEnvoi = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"message:read"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"message:read"})
     */
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="message", orphanRemoval=true)
     */
    private Collection $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->dateEnvoi = new \DateTime();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getExpediteur(): ?User
    {
        return $this->expediteur;
    }

    public function setExpediteur(?User $expediteur): static
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isLu(): bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): static
    {
        $this->lu = $lu;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setMessage($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getMessage() === $this) {
                $notification->setMessage(null);
            }
        }
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestamps(): void
    {
        if ($this->dateEnvoi === null) {
            $this->dateEnvoi = new \DateTime();
        }
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}