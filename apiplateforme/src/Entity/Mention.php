<?php

namespace App\Entity;

use App\Repository\MentionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use DateTimeImmutable;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"name": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"mention:read"}},
 *     denormalizationContext={"groups"={"mention:write"}},
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
 *     "profs.nom": "partial",
 *     "etudiants.nom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "created_at"})
 * @ORM\Entity(repositoryClass=MentionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Mention
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"mention:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mention:read", "mention:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"mention:read", "mention:write"})
     * @Assert\Length(max=255)
     */
    private ?string $icon = null;

    /**
     * @Vich\UploadableField(mapping="mention_icon", fileNameProperty="icon")
     * @Assert\File(
     *     maxSize="2M",
     *     mimeTypes={"image/jpeg", "image/png", "image/gif"}
     * )
     * @Groups({"mention:write"})
     */
    private ?File $iconFile = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $iconUpdatedAt = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"mention:read"})
     */
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"mention:read"})
     */
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @ORM\OneToMany(targetEntity=Prof::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $profs;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=Ue::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $ues;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $agendas;

    /**
     * @ORM\OneToMany(targetEntity=Bibliotheque::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $bibliotheques;

    /**
     * @ORM\OneToMany(targetEntity=NotificationGroupe::class, mappedBy="mention")
     * @Groups({"mention:read"})
     */
    private Collection $notificationGroupes;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->ues = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->bibliotheques = new ArrayCollection();
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIconFile(): ?File
    {
        return $this->iconFile;
    }

    public function setIconFile(?File $iconFile = null): self
    {
        $this->iconFile = $iconFile;
        if ($iconFile) {
            $this->iconUpdatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getIconUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->iconUpdatedAt;
    }

    public function setIconUpdatedAt(?\DateTimeImmutable $iconUpdatedAt): self
    {
        $this->iconUpdatedAt = $iconUpdatedAt;
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
            $prof->setMention($this);
        }
        return $this;
    }

    public function removeProf(Prof $prof): self
    {
        if ($this->profs->removeElement($prof)) {
            if ($prof->getMention() === $this) {
                $prof->setMention(null);
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
            $etudiant->setMention($this);
        }
        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            if ($etudiant->getMention() === $this) {
                $etudiant->setMention(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Ue>
     */
    public function getUes(): Collection
    {
        return $this->ues;
    }

    public function addUe(Ue $ue): self
    {
        if (!$this->ues->contains($ue)) {
            $this->ues[] = $ue;
            $ue->setMention($this);
        }
        return $this;
    }

    public function removeUe(Ue $ue): self
    {
        if ($this->ues->removeElement($ue)) {
            if ($ue->getMention() === $this) {
                $ue->setMention(null);
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
            $agenda->setMention($this);
        }
        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            if ($agenda->getMention() === $this) {
                $agenda->setMention(null);
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
            $bibliotheque->setMention($this);
        }
        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): self
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            if ($bibliotheque->getMention() === $this) {
                $bibliotheque->setMention(null);
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

    public function addNotificationGroupe(NotificationGroupe $notificationGroupe): self
    {
        if (!$this->notificationGroupes->contains($notificationGroupe)) {
            $this->notificationGroupes->add($notificationGroupe);
            $notificationGroupe->setMention($this);
        }
        return $this;
    }

    public function removeNotificationGroupe(NotificationGroupe $notificationGroupe): self
    {
        if ($this->notificationGroupes->removeElement($notificationGroupe)) {
            if ($notificationGroupe->getMention() === $this) {
                $notificationGroupe->setMention(null);
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