<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
 *         "normalization_context"={"groups"={"user:read"}},
 *         "denormalization_context"={"groups"={"user:write"}}
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_ADMIN')"
 *         },
 *         "post"={
 *             "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
 *             "validation_groups"={"Default", "user:create"}
 *         }
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_ADMIN') or object == user"
 *         },
 *         "put"={
 *             "security"="is_granted('ROLE_ADMIN') or object == user"
 *         },
 *         "patch"={
 *             "security"="is_granted('ROLE_ADMIN') or object == user"
 *         },
 *         "delete"={
 *             "security"="is_granted('ROLE_ADMIN')"
 *         }
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "email": "partial",
 *     "name": "partial",
 *     "ville": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "email", "name", "created_at"})
 * @ApiFilter(BooleanFilter::class, properties={"status"})
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=180)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"user:read", "user:write"})
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     * @Groups({"user:write"})
     * @Assert\NotBlank(groups={"user:create"})
     * @Assert\Length(min=6)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60)
     * @Groups({"user:read", "user:write"})
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=60)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=20)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=125)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=255)
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"user:read", "user:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=255)
     */
    private $adresse;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Province")
    * @ORM\JoinColumn(name="province_id", referencedColumnName="id")
    * @Groups({"user:read", "user:write"})
    */
    private $province;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    private $preferences_notification;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"user:read"})
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=Prof::class, mappedBy="user")
     */
    private $profs;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="user")
     */
    private $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="auteur")
     */
    private $agendas;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="user")
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="user")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity=FichierSupport::class, mappedBy="auteur")
     */
    private $fichierSupports;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="createdBy")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="expediteur")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="user")
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity=AbonnementNotification::class, mappedBy="user")
     */
    private $abonnementNotifications;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->fichierSupports = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->abonnementNotifications = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

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
            $prof->setUser($this);
        }

        return $this;
    }

    public function removeProf(Prof $prof): self
    {
        if ($this->profs->removeElement($prof)) {
            // set the owning side to null (unless already changed)
            if ($prof->getUser() === $this) {
                $prof->setUser(null);
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
            $etudiant->setUser($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getUser() === $this) {
                $etudiant->setUser(null);
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
            $agenda->setAuteur($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getAuteur() === $this) {
                $agenda->setAuteur(null);
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
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setUser($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getUser() === $this) {
                $document->setUser(null);
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
            $fichierSupport->setAuteur($this);
        }

        return $this;
    }

    public function removeFichierSupport(FichierSupport $fichierSupport): self
    {
        if ($this->fichierSupports->removeElement($fichierSupport)) {
            // set the owning side to null (unless already changed)
            if ($fichierSupport->getAuteur() === $this) {
                $fichierSupport->setAuteur(null);
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
            $conversation->setCreatedBy($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getCreatedBy() === $this) {
                $conversation->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setExpediteur($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getExpediteur() === $this) {
                $message->setExpediteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AbonnementNotification>
     */
    public function getAbonnementNotifications(): Collection
    {
        return $this->abonnementNotifications;
    }

    public function addAbonnementNotification(AbonnementNotification $abonnementNotification): static
    {
        if (!$this->abonnementNotifications->contains($abonnementNotification)) {
            $this->abonnementNotifications->add($abonnementNotification);
            $abonnementNotification->setUser($this);
        }

        return $this;
    }

    public function removeAbonnementNotification(AbonnementNotification $abonnementNotification): static
    {
        if ($this->abonnementNotifications->removeElement($abonnementNotification)) {
            // set the owning side to null (unless already changed)
            if ($abonnementNotification->getUser() === $this) {
                $abonnementNotification->setUser(null);
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
