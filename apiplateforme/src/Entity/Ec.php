<?php

namespace App\Entity;

use App\Repository\EcRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"name": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"ec:read"}},
 *     denormalizationContext={"groups"={"ec:write"}},
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
 *     "code": "exact",
 *     "name": "partial",
 *     "ue.id": "exact",
 *     "prof.id": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "code", "name", "coeff", "created_at"})
 * @ApiFilter(BooleanFilter::class, properties={"status"})
 * @ORM\Entity(repositoryClass=EcRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Ec
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"ec:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"ec:read", "ec:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"ec:read", "ec:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"ec:read", "ec:write"})
     * @Assert\NotBlank
     * @Assert\Positive
     */
    private $coeff;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"ec:read", "ec:write"})
     */
    private $status = true;

    /**
     * @ORM\ManyToOne(targetEntity=Prof::class, inversedBy="ecs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ec:read", "ec:write"})
     * @Assert\NotNull
     */
    private $prof;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"ec:read", "ec:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"ec:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"ec:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Ue::class, inversedBy="ecs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ec:read", "ec:write"})
     * @Assert\NotNull
     */
    private $ue;

    /**
     * @ORM\OneToMany(mappedBy="ec", targetEntity=Bibliotheque::class)
     */
    private $bibliotheques;

    /**
     * @ORM\OneToMany(mappedBy="ec", targetEntity=Commentaire::class)
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(mappedBy="ec", targetEntity=FichierSupport::class)
     */
    private $fichierSupports;

    /**
     * @ORM\OneToMany(mappedBy="ec", targetEntity=Conversation::class)
     */
    private $conversations;

    /**
     * @ORM\OneToMany(mappedBy="ec", targetEntity=NotificationGroupe::class)
     */
    private $notificationGroupes;

    /**
     * @ORM\OneToMany(targetEntity=Examen::class, mappedBy="ec")
     */
    private Collection $examens;

    public function __construct()
    {
        $this->bibliotheques = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->fichierSupports = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->notificationGroupes = new ArrayCollection();
        $this->examens = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
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

    public function getCoeff(): ?int
    {
        return $this->coeff;
    }

    public function setCoeff(int $coeff): self
    {
        $this->coeff = $coeff;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;
        return $this;
    }

    public function getProf(): ?Prof
    {
        return $this->prof;
    }

    public function setProf(?Prof $prof): self
    {
        $this->prof = $prof;
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
            $bibliotheque->setEc($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): self
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getEc() === $this) {
                $bibliotheque->setEc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setEc($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getEc() === $this) {
                $commentaire->setEc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FichierSupport>
     */
    public function getFichierSupports(): Collection
    {
        return $this->fichierSupports;
    }

    public function addFichierSupport(FichierSupport $fichierSupport): self
    {
        if (!$this->fichierSupports->contains($fichierSupport)) {
            $this->fichierSupports[] = $fichierSupport;
            $fichierSupport->setEc($this);
        }

        return $this;
    }

    public function removeFichierSupport(FichierSupport $fichierSupport): self
    {
        if ($this->fichierSupports->removeElement($fichierSupport)) {
            // set the owning side to null (unless already changed)
            if ($fichierSupport->getEc() === $this) {
                $fichierSupport->setEc(null);
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
            $conversation->setEc($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getEc() === $this) {
                $conversation->setEc(null);
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
            $notificationGroupe->setEc($this);
        }

        return $this;
    }

    public function removeNotificationGroupe(NotificationGroupe $notificationGroupe): static
    {
        if ($this->notificationGroupes->removeElement($notificationGroupe)) {
            // set the owning side to null (unless already changed)
            if ($notificationGroupe->getEc() === $this) {
                $notificationGroupe->setEc(null);
            }
        }

        return $this;
    }

    public function getExamens(): Collection
    {
        return $this->examens;
    }

    public function addExamen(Examen $examen): self
    {
        if (!$this->examens->contains($examen)) {
            $this->examens[] = $examen;
            $examen->setEc($this);
        }
        return $this;
    }

    public function removeExamen(Examen $examen): self
    {
        if ($this->examens->removeElement($examen)) {
            if ($examen->getEc() === $this) {
                $examen->setEc(null);
            }
        }
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}


