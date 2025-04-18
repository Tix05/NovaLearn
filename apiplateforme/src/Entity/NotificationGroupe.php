<?php

namespace App\Entity;

use App\Repository\NotificationGroupeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationGroupeRepository::class)]
class NotificationGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notificationGroupes')]
    private ?Notification $notification = null;

    #[ORM\ManyToOne(inversedBy: 'notificationGroupes')]
    private ?Parcours $parcours = null;

    #[ORM\ManyToOne(inversedBy: 'notificationGroupes')]
    private ?Niveau $niveau = null;

    #[ORM\ManyToOne(inversedBy: 'notificationGroupes')]
    private ?Mention $mention = null;

    #[ORM\ManyToOne(inversedBy: 'notificationGroupes')]
    private ?Ec $ec = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

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
}
