<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ParticipantConversation
{
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_MEMBRE = 'MEMBRE';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conversation;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;
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

    public function __toString(): string
    {
        return sprintf('ParticipantConversation#%d (User: %s, Conversation: %d)', $this->id, $this->user?->getName() ?? 'N/A', $this->conversation?->getId() ?? 0);
    }
}