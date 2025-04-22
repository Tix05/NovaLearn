<?php

namespace App\Entity;

use App\Repository\ParticipantConversationRepository;
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
 *         "order"={"createdAt": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"participant_conversation:read"}},
 *     denormalizationContext={"groups"={"participant_conversation:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "role": "exact",
 *     "conversation.id": "exact",
 *     "user.id": "exact",
 *     "user.nom": "partial",
 *     "user.prenom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "dateDernierVu", "createdAt"})
 * @ApiFilter(DateFilter::class, properties={"dateDernierVu", "createdAt", "updatedAt"})
 * @ORM\Entity(repositoryClass=ParticipantConversationRepository::class)
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="participant_unique", columns={"conversation_id", "user_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class ParticipantConversation
{
    public const ROLE_MEMBRE = 'MEMBRE';
    public const ROLE_PROFESSEUR = 'PROFESSEUR';
    public const ROLE_ADMIN = 'ADMIN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"participant_conversation:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"participant_conversation:read", "participant_conversation:write"})
     * @Assert\NotNull
     */
    private ?Conversation $conversation = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="conversationParticipants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"participant_conversation:read", "participant_conversation:write"})
     * @Assert\NotNull
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"participant_conversation:read", "participant_conversation:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     ParticipantConversation::ROLE_MEMBRE,
     *     ParticipantConversation::ROLE_PROFESSEUR,
     *     ParticipantConversation::ROLE_ADMIN
     * })
     */
    private ?string $role = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"participant_conversation:read", "participant_conversation:write"})
     */
    private ?\DateTimeInterface $dateDernierVu = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"participant_conversation:read"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"participant_conversation:read"})
     */
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
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

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getDateDernierVu(): ?\DateTimeInterface
    {
        return $this->dateDernierVu;
    }

    public function setDateDernierVu(?\DateTimeInterface $dateDernierVu): self
    {
        $this->dateDernierVu = $dateDernierVu;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}