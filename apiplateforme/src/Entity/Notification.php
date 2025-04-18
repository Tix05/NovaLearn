<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $lien = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?Message $message = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?Agenda $agenda = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?Document $document = null;

    #[ORM\Column]
    private ?bool $lue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLecture = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'notification', targetEntity: NotificationGroupe::class)]
    private Collection $notificationGroupes;

    public function __construct()
    {
        $this->notificationGroupes = new ArrayCollection();
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
}
