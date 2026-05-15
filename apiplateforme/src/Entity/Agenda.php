<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"date": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"agenda:read"}},
 *     denormalizationContext={"groups"={"agenda:write"}},
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
 *     "titre": "partial",
 *     "description": "partial",
 *     "type": "exact",
 *     "auteur.id": "exact",
 *     "mention.id": "exact",
 *     "niveau.id": "exact",
 *     "parcours.id": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "titre", "date", "date_expiration"})
 * @ApiFilter(DateFilter::class, properties={"date", "date_expiration"})
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Agenda
{
    public const TYPE_COURS = 'COURS';
    public const TYPE_EVENEMENT = 'EVENEMENT';
    public const TYPE_EXAMEN = 'EXAMEN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"agenda:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     */
    private ?\DateTimeImmutable $date = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     * @Assert\Expression(
     *     "this.getDate() <= this.getDateExpiration()",
     *     message="La date d'expiration doit être postérieure ou égale à la date de début"
     * )
     */
    private ?\DateTimeImmutable $date_expiration = null;

    /**
     * @ORM\Column(type="text")
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    private $video;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices={Agenda::TYPE_COURS, Agenda::TYPE_EVENEMENT, Agenda::TYPE_EXAMEN},
     *     message="Choisissez un type valide: COURS, EVENEMENT ou EXAMEN"
     * )
     */
    private $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"agenda:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"agenda:read"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $nom_auteur;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="agendas")
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\Expression(
     *     "this.getType() in ['COURS', 'EXAMEN'] ? this.getMention() != null : true",
     *     message="La mention est requise pour les types COURS et EXAMEN"
     * )
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="agendas")
     * @Groups({"agenda:read", "agenda:write"})
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="agendas")
     * @Groups({"agenda:read", "agenda:write"})
     * @Assert\Expression(
     *     "this.getType() in ['COURS', 'EXAMEN'] ? this.getNiveau() != null : true",
     *     message="Le niveau est requis pour les types COURS et EXAMEN"
     * )
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="agenda")
     */
    private Collection $notifications;

    /**
     * @ORM\OneToMany(targetEntity=Examen::class, mappedBy="agenda")
     */
    private Collection $examens;

    /**
     * @Vich\UploadableField(mapping="agenda_image", fileNameProperty="image")
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png", "image/gif"}
     * )
     * @Groups({"agenda:write"})
     */
    private $imageFile;

    /**
     * @Vich\UploadableField(mapping="agenda_video", fileNameProperty="video")
     * @Assert\File(
     *     maxSize="50M",
     *     mimeTypes={"video/mp4", "video/quicktime", "video/x-msvideo"}
     * )
     * @Groups({"agenda:write"})
     */
    private $videoFile;

    /**
     * @ORM\OneToOne(targetEntity=Bibliotheque::class, inversedBy="agenda")
     * @ORM\JoinColumn(name="bibliotheque_id", referencedColumnName="id", nullable=true)
     */
    private $bibliotheque;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();
        $this->examens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(\DateTimeImmutable $date_expiration): self
    {
        $this->date_expiration = $date_expiration;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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

    public function setNomAuteur(string $nom_auteur): self
    {
        $this->nom_auteur = $nom_auteur;
        return $this;
    }

    public function getNomAuteur(): ?string
    {
        return $this->nom_auteur;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;
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

    public function getBibliotheque(): ?Bibliotheque
    {
        return $this->bibliotheque;
    }

    public function setBibliotheque(?Bibliotheque $bibliotheque): self
    {
        $this->bibliotheque = $bibliotheque;
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTimeImmutable();
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
            $notification->setAgenda($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getAgenda() === $this) {
                $notification->setAgenda(null);
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
            $examen->setAgenda($this);
        }
        return $this;
    }

    public function removeExamen(Examen $examen): self
    {
        if ($this->examens->removeElement($examen)) {
            if ($examen->getAgenda() === $this) {
                $examen->setAgenda(null);
            }
        }
        return $this;
    }

    public function setImageFile($imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updated_at = new \DateTimeImmutable();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setVideoFile($videoFile = null): void
    {
        $this->videoFile = $videoFile;
        if (null !== $videoFile) {
            $this->updated_at = new \DateTimeImmutable();
        }
    }

    public function getVideoFile()
    {
        return $this->videoFile;
    }
}
