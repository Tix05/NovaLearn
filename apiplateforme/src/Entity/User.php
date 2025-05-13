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
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

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
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=180)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="json")
     * @Groups({"user:read", "user:write"})
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     * @Groups({"user:write"})
     * @Assert\NotBlank(groups={"user:create"})
     * @Assert\Length(min=6)
     */
    private ?string $password = null;

    /**
     * @ORM\Column(type="string", length=60)
     * @Groups({"user:read", "user:write"})
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=60)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=20)
     */
    private ?string $telephone = null;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=125)
     */
    private ?string $avatar = null;

    /**
     * @Vich\UploadableField(mapping="user_avatar", fileNameProperty="avatar")
     * @Assert\File(
     *     maxSize="2M",
     *     mimeTypes={"image/jpeg", "image/png", "image/gif"}
     * )
     */
    private ?File $avatarFile = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=255)
     */
    private ?string $ville = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $reset_token = null;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"user:read", "user:write"})
     */
    private bool $status = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Length(max=255)
     */
    private ?string $adresse = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Province")
     * @ORM\JoinColumn(name="province_id", referencedColumnName="id")
     * @Groups({"user:read", "user:write"})
     */
    private ?Province $province = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    private ?array $preferences_notification = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user:read"})
     */
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"user:read"})
     */
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @ORM\OneToMany(targetEntity=Prof::class, mappedBy="user")
     */
    private Collection $profs;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="user")
     */
    private Collection $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="user")
     */
    private Collection $commentaires;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="user")
     */
    private Collection $documents;

    /**
     * @ORM\OneToMany(targetEntity=FichierSupport::class, mappedBy="auteur")
     */
    private Collection $fichierSupports;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="createdBy")
     */
    private Collection $conversations;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="expediteur")
     */
    private Collection $messages;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="user")
     */
    private Collection $notifications;

    /**
     * @ORM\OneToMany(targetEntity=AbonnementNotification::class, mappedBy="user")
     */
    private Collection $abonnementNotifications;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantConversation::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $conversationParticipants;

    /**
     * @ORM\OneToMany(targetEntity=Examen::class, mappedBy="auteur")
     */
    private Collection $examensCrees;

    public function __construct()
    {
        $this->profs = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->fichierSupports = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->abonnementNotifications = new ArrayCollection();
        $this->conversationParticipants = new ArrayCollection();
        $this->examensCrees = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
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

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(?File $avatarFile = null): self
    {
        $this->avatarFile = $avatarFile;
        if ($avatarFile) {
            $this->avatarUpdatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getAvatarUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->avatarUpdatedAt;
    }

    public function setAvatarUpdatedAt(?\DateTimeImmutable $avatarUpdatedAt): self
    {
        $this->avatarUpdatedAt = $avatarUpdatedAt;
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

    public function getPreferencesNotification(): ?array
    {
        return $this->preferences_notification;
    }

    public function setPreferencesNotification(?array $preferences_notification): self
    {
        $this->preferences_notification = $preferences_notification;
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
            if ($etudiant->getUser() === $this) {
                $etudiant->setUser(null);
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

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->setCreatedBy($this);
        }
        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
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

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setExpediteur($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
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

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
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

    public function addAbonnementNotification(AbonnementNotification $abonnementNotification): self
    {
        if (!$this->abonnementNotifications->contains($abonnementNotification)) {
            $this->abonnementNotifications[] = $abonnementNotification;
            $abonnementNotification->setUser($this);
        }
        return $this;
    }

    public function removeAbonnementNotification(AbonnementNotification $abonnementNotification): self
    {
        if ($this->abonnementNotifications->removeElement($abonnementNotification)) {
            if ($abonnementNotification->getUser() === $this) {
                $abonnementNotification->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ParticipantConversation>
     */
    public function getConversationParticipants(): Collection
    {
        return $this->conversationParticipants;
    }

    public function addConversationParticipant(ParticipantConversation $conversationParticipant): self
    {
        if (!$this->conversationParticipants->contains($conversationParticipant)) {
            $this->conversationParticipants[] = $conversationParticipant;
            $conversationParticipant->setUser($this);
        }
        return $this;
    }

    public function removeConversationParticipant(ParticipantConversation $conversationParticipant): self
    {
        if ($this->conversationParticipants->removeElement($conversationParticipant)) {
            if ($conversationParticipant->getUser() === $this) {
                $conversationParticipant->setUser(null);
            }
        }
        return $this;
    }

    public function getExamensCrees(): Collection
    {
        return $this->examensCrees;
    }

    public function addExamensCree(Examen $examensCree): self
    {
        if (!$this->examensCrees->contains($examensCree)) {
            $this->examensCrees[] = $examensCree;
            $examensCree->setAuteur($this);
        }
        return $this;
    }

    public function removeExamensCree(Examen $examensCree): self
    {
        if ($this->examensCrees->removeElement($examensCree)) {
            if ($examensCree->getAuteur() === $this) {
                $examensCree->setAuteur(null);
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