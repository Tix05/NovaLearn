<?php

namespace App\Entity;

use App\Repository\ProfRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()  U
 * @ORM\Entity(repositoryClass="App\Repository\ProfRepository")
 */
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'profs')]
    private $user;

    #[ORM\ManyToOne(targetEntity: Mention::class, inversedBy: 'profs')]
    private $mention;

    #[ORM\ManyToOne(targetEntity: Ec::class, inversedBy: 'profs')]
    private $ec;

    #[ORM\ManyToOne(targetEntity: Parcours::class, inversedBy: 'profs')]
    private $parcours;

    #[ORM\OneToMany(mappedBy: 'prof', targetEntity: Ec::class)]
    private $ecs;

    public function __construct()
    {
        $this->ecs = new ArrayCollection();
    }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

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

    /**
     * @return Collection<int, Ec>
     */
    public function getEcs(): Collection
    {
        return $this->ecs;
    }

    public function addEc(Ec $ec): self
    {
        if (!$this->ecs->contains($ec)) {
            $this->ecs[] = $ec;
            $ec->setProf($this);
        }

        return $this;
    }

    public function removeEc(Ec $ec): self
    {
        if ($this->ecs->removeElement($ec)) {
            // set the owning side to null (unless already changed)
            if ($ec->getProf() === $this) {
                $ec->setProf(null);
            }
        }

        return $this;
    }
}
