<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"updatedAt": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"conversation:read"}},
 *     denormalizationContext={"groups"={"conversation:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getCreatedBy() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getCreatedBy() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "sujet": "partial",
 *     "type": "exact",
 *     "ec.nom": "partial",
 *     "parcours.nom": "partial",
 *     "createdBy.id": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "sujet", "createdAt", "updatedAt"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "updatedAt"})
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Conversation
{
    public const TYPE_PRIVEE = 'PRIVEE';
    public const TYPE_GROUPE_FILIERE = 'GROUPE_FILIERE';
    public const TYPE_GROUPE_COURS = 'GROUPE_COURS';
    public const TYPE_ADMIN = 'ADMIN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"conversation:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"conversation:read", "conversation:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $sujet;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"conversation:read", "conversation:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     Conversation::TYPE_PRIVEE,
     *     Conversation::TYPE_GROUPE_FILIERE,
     *     Conversation::TYPE_GROUPE_COURS,
     *     Conversation::TYPE_ADMIN
     * })
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class, inversedBy="conversations")
     * @Groups({"conversation:read", "conversation:write"})
     */
    private $ec;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="conversations")
     * @Groups({"conversation:read", "conversation:write"})
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="conversations")
     * @Groups({"conversation:read"})
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"conversation:read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"conversation:read"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="conversation")
     * @Groups({"conversation:read"})
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="conversation")
     */
    private $notifications;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setConversation($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getConversation() === $this) {
                $notification->setConversation(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestamps(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}