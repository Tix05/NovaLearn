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
 *     attributes={
 *         "order"={"name": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"parcours:read"}},
 *     denormalizationContext={"groups"={"parcours:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN')"},
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 *     "full_name": "partial",
 *     "mention.name": "partial",
 *     "profs.nom": "partial",
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
     * @Groups({"parcours:read", "parcours:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"parcours:read", "parcours:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $full_name;

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
     * @ORM\OneToMany(targetEntity=Prof::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $profs;

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
     * @Groups({"parcours:read"})
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity=NotificationGroupe::class, mappedBy="parcours")
     * @Groups({"parcours:read"})
     */
    private $notificationGroupes;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->bibliotheques = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->notificationGroupes = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
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
     * @return Collection<int, Prof>
     */
    public function getProfs(): Collection
    {
        return $this->profs;
    }

    public function addProf(Prof $prof): self
    {
        if (!$this->profs->contains($prof)) {
            $this->profs[] = $prof;
            $prof->setParcours($this);
        }

        return $this;
    }

    public function removeProf(Prof $prof): self
    {
        if ($this->profs->removeElement($prof)) {
            // set the owning side to null (unless already changed)
            if ($prof->getParcours() === $this) {
                $prof->setParcours(null);
            }
        }

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

