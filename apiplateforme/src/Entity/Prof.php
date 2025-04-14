<?php

namespace App\Entity;

use App\Repository\ProfRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfRepository::class)]
class Prof
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $user_id;

    #[ORM\Column(type: 'integer')]
    private $mention_id;

    #[ORM\Column(type: 'integer')]
    private $ec_id;

    #[ORM\Column(type: 'integer')]
    private $parcours_id;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMentionId(): ?int
    {
        return $this->mention_id;
    }

    public function setMentionId(int $mention_id): self
    {
        $this->mention_id = $mention_id;

        return $this;
    }

    public function getEcId(): ?int
    {
        return $this->ec_id;
    }

    public function setEcId(int $ec_id): self
    {
        $this->ec_id = $ec_id;

        return $this;
    }

    public function getParcoursId(): ?int
    {
        return $this->parcours_id;
    }

    public function setParcoursId(int $parcours_id): self
    {
        $this->parcours_id = $parcours_id;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
