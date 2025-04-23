<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"dateCreation": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"notification:read"}},
 *     denormalizationContext={"groups"={"notification:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"},
 *         "patch"={
 *             "security"="is_granted('ROLE_ADMIN') or object.getUser() == user",
 *             "method"="PATCH",
 *             "path"="/notifications/{id}/mark-as-read",
 *             "controller"=App\Controller\NotificationReadController::class,
 *             "openapi_context"={
 *                 "summary"="Marquer une notification comme lue",
 *                 "requestBody"={
 *                     "content"={
 *                         "application/json"={
 *                             "schema"={}
 *                         }
 *                     }
 *                 }
 *             }
 *         },
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "titre": "partial",
 *     "contenu": "partial",
 *     "type": "exact",
 *     "user.id": "exact",
 *     "lue": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={
 *     "id", "dateCreation", "dateLecture", "createdAt"
 * })
 * @ApiFilter(DateFilter::class, properties={
 *     "dateCreation", "dateLecture", "createdAt"
 * })
 * @ApiFilter(BooleanFilter::class, properties={"lue"})
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Notification
{
    public const TYPE_MESSAGE_PRIVE = 'MESSAGE_PRIVE';
    public const TYPE_MESSAGE_GROUPE = 'MESSAGE_GROUPE';
    public const TYPE_ANNONCE_PROF = 'ANNONCE_PROF';
    public const TYPE_ANNONCE_ADMIN = 'ANNONCE_ADMIN';
    public const TYPE_DOCUMENT_DISPONIBLE = 'DOCUMENT_DISPONIBLE';
    public const TYPE_RAPPEL_AGENDA = 'RAPPEL_AGENDA';
    public const TYPE_VALIDATION_DOCUMENT = 'VALIDATION_DOCUMENT';
    public const TYPE_AUTRE = 'AUTRE';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     * @Groups({"notification:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"notification:read", "notification:write"})
     * @Assert\NotNull
     */
    private ?User $user = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"notification:read", "notification:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $titre = null;

    /**
     * @ORM\Column(type=Types::TEXT)
     * @Groups({"notification:read", "notification:write"})
     * @Assert\NotBlank
     */
    private ?string $contenu = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"notification:read", "notification:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     Notification::TYPE_MESSAGE_PRIVE,
     *     Notification::TYPE_MESSAGE_GROUPE,
     *     Notification::TYPE_ANNONCE_PROF,
     *     Notification::TYPE_ANNONCE_ADMIN,
     *     Notification::TYPE_DOCUMENT_DISPONIBLE,
     *     Notification::TYPE_RAPPEL_AGENDA,
     *     Notification::TYPE_VALIDATION_DOCUMENT,
     *     Notification::TYPE_AUTRE
     * })
     */
    private ?string $type = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"notification:read", "notification:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $lien = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications")
     * @Groups({"notification:read", "notification:write"})
     */
    private ?Conversation $conversation = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications")
     * @Groups({"notification:read", "notification:write"})
     */
    private ?Message $message = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications")
     * @Groups({"notification:read", "notification:write"})
     */
    private ?Agenda $agenda = null;

    /**
     * @ORM\ManyToOne(inversedBy="notifications")
     * @Groups({"notification:read", "notification:write"})
     */
    private ?Document $document = null;

    /**
     * @ORM\Column
     * @Groups({"notification:read", "notification:write"})
     */
    private ?bool $lue = false;

    /**
     * @ORM\Column(type=Types::DATETIME_MUTABLE)
     * @Groups({"notification:read"})
     */
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * @ORM\Column(type=Types::DATETIME_MUTABLE, nullable=true)
     * @Groups({"notification:read"})
     */
    private ?\DateTimeInterface $dateLecture = null;

    /**
     * @ORM\Column
     * @Groups({"notification:read"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column
     * @Groups({"notification:read"})
     */
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\OneToMany(mappedBy="notification", targetEntity=NotificationGroupe::class)
     */
    private Collection $notificationGroupes;

    public function __construct()
    {
        $this->notificationGroupes = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(string $lien): static
    {
        $this->lien = $lien;

        return $this;
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

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): static
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function isLue(): ?bool
    {
        return $this->lue;
    }

    public function setLue(bool $lue): static
    {
        $this->lue = $lue;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateLecture(): ?\DateTimeInterface
    {
        return $this->dateLecture;
    }

    public function setDateLecture(\DateTimeInterface $dateLecture): static
    {
        $this->dateLecture = $dateLecture;

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

    /**
     * @return Collection<int, NotificationGroupe>
     */
    public function getNotificationGroupes(): Collection
    {
        return $this->notificationGroupes;
    }

    public function addNotificationGroupe(NotificationGroupe $notificationGroupe): static
    {
        if (!$this->notificationGroupes->contains($notificationGroupe)) {
            $this->notificationGroupes->add($notificationGroupe);
            $notificationGroupe->setNotification($this);
        }

        return $this;
    }

    public function removeNotificationGroupe(NotificationGroupe $notificationGroupe): static
    {
        if ($this->notificationGroupes->removeElement($notificationGroupe)) {
            // set the owning side to null (unless already changed)
            if ($notificationGroupe->getNotification() === $this) {
                $notificationGroupe->setNotification(null);
            }
        }

        return $this;
    }
    public function markAsRead(): void
    {
        $this->lue = true;
        $this->dateLecture = new \DateTimeImmutable();
    }
    /**
     * @ORM\PrePersist
     */
    public function setDefaultValues(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
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
