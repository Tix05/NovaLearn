<?php

namespace App\Entity;

use App\Repository\ParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"={"normalization_context"={"groups"={"parcours:read"}}},
 *         "post"={
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "denormalization_context"={"groups"={"parcours:write"}}
 *         }
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"={"groups"={"parcours:read"}}},
 *         "put"={
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "denormalization_context"={"groups"={"parcours:write"}}
 *         },
 *         "patch"={
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "denormalization_context"={"groups"={"parcours:write"}}
 *         },
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 *     "full_name": "partial",
 *     "mention.name": "partial",
 *     "etudiants.nom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "full_name", "created_at"})
 * @ORM\Entity(repositoryClass=ParcoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Parcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"parcours:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"parcours:read", "parcours:write", "ue:read"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"parcours:read", "parcours:write", "ue:read"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $full_name;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"parcours:read", "parcours:write", "bibliotheque:read"})
     * @Assert\NotNull
     */
    private $niveau;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"parcours:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"parcours:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="parcours")
     * @Groups({"parcours:read", "parcours:write"})
     */
    private $mention;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $agendas;

    /**
     * @ORM\OneToMany(targetEntity=Bibliotheque::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $bibliotheques;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="parcours")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity=NotificationGroupe::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $notificationGroupes;

    /**
     * @ORM\OneToMany(targetEntity=UeParcours::class, mappedBy="parcours")
     */
    private $ueParcours;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->bibliotheques = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->notificationGroupes = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
        $this->ueParcours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;
        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;
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

    /**
     * @return Collection<int, Etudiant>
     */
    public function getEtudiants(): Collection
    {
        return $this->etudiants;
    }

    public function addEtudiant(Etudiant $etudiant): self
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants[] = $etudiant;
            $etudiant->setParcours($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getParcours() === $this) {
                $etudiant->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Agenda>
     */
    public function getAgendas(): Collection
    {
        return $this->agendas;
    }

    public function addAgenda(Agenda $agenda): self
    {
        if (!$this->agendas->contains($agenda)) {
            $this->agendas[] = $agenda;
            $agenda->setParcours($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getParcours() === $this) {
                $agenda->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bibliotheque>
     */
    public function getBibliotheques(): Collection
    {
        return $this->bibliotheques;
    }

    public function addBibliotheque(Bibliotheque $bibliotheque): self
    {
        if (!$this->bibliotheques->contains($bibliotheque)) {
            $this->bibliotheques[] = $bibliotheque;
            $bibliotheque->setParcours($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): self
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getParcours() === $this) {
                $bibliotheque->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setParcours($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getParcours() === $this) {
                $conversation->setParcours(null);
            }
        }

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
            $notificationGroupe->setParcours($this);
        }

        return $this;
    }

    public function removeNotificationGroupe(NotificationGroupe $notificationGroupe): static
    {
        if ($this->notificationGroupes->removeElement($notificationGroupe)) {
            // set the owning side to null (unless already changed)
            if ($notificationGroupe->getParcours() === $this) {
                $notificationGroupe->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UeParcours[]
     */
    public function getUeParcours(): Collection
    {
        return $this->ueParcours;
    }

    public function addUeParcours(UeParcours $ueParcours): self
    {
        if (!$this->ueParcours->contains($ueParcours)) {
            $this->ueParcours[] = $ueParcours;
            $ueParcours->setParcours($this);
        }
        return $this;
    }

    public function removeUeParcours(UeParcours $ueParcours): self
    {
        if ($this->ueParcours->removeElement($ueParcours)) {
            if ($ueParcours->getParcours() === $this) {
                $ueParcours->setParcours(null);
            }
        }
        return $this;
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

