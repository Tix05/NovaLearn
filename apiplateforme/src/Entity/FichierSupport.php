<?php

namespace App\Entity;

use App\Repository\FichierSupportRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ApiResource(
 *     attributes={
 *         "order"={"date_ajout": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"fichier_support:read"}},
 *     denormalizationContext={"groups"={"fichier_support:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "method"="POST",
 *             "path"="/fichier_supports/upload",
 *             "controller"=App\Controller\FichierSupportUploadController::class,
 *             "deserialize"=false,
 *             "security"="is_granted('ROLE_PROF')",
 *             "openapi_context"={
 *                 "requestBody"={
 *                     "content"={
 *                         "multipart/form-data"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "file"={"type"="string", "format"="binary"},
 *                                     "titre"={"type"="string"},
 *                                     "type"={"type"="string"},
 *                                     "ec"={"type"="integer"},
 *                                     "description"={"type"="string"},
 *                                     "est_publique"={"type"="boolean"},
 *                                     "url"={"type"="string"}
 *                                 },
 *                                 "required"={"titre", "type", "ec"}
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getAuteur() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getAuteur() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "titre": "partial",
 *     "type": "exact",
 *     "ec.nom": "partial",
 *     "auteur.nom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "titre", "date_ajout"})
 * @ApiFilter(DateFilter::class, properties={"date_ajout"})
 * @ApiFilter(BooleanFilter::class, properties={"est_publique"})
 * @ORM\Entity(repositoryClass=FichierSupportRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class FichierSupport
{
    public const TYPE_FICHIER = 'FICHIER';
    public const TYPE_VIDEO = 'VIDEO';
    public const TYPE_LIEN = 'LIEN';
    public const TYPE_AUDIO = 'AUDIO';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"fichier_support:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"fichier_support:read", "fichier_support:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"fichier_support:read", "fichier_support:write"})
     * @Assert\NotBlank
     * @Assert\Choice({
     *     FichierSupport::TYPE_FICHIER,
     *     FichierSupport::TYPE_VIDEO,
     *     FichierSupport::TYPE_LIEN,
     *     FichierSupport::TYPE_AUDIO
     * })
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"fichier_support:read"})
     */
    private $fichier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"fichier_support:read", "fichier_support:write"})
     * @Assert\Url(groups={"url_required"})
     * @Assert\Length(max=255)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"fichier_support:read", "fichier_support:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"fichier_support:read"})
     */
    private $date_ajout;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"fichier_support:read", "fichier_support:write"})
     */
    private $est_publique = true;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"fichier_support:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"fichier_support:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class, inversedBy="fichierSupports")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"fichier_support:read", "fichier_support:write"})
     * @Assert\NotNull
     */
    private $ec;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="fichierSupports")
     * @Groups({"fichier_support:read"})
     */
    private $auteur;

    public function __construct()
    {
        $this->date_ajout = new \DateTime();
        $this->created_at = new \DateTimeImmutable();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updated_at = new \DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): self
    {
        $this->fichier = $fichier;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDateAjout(\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;
        return $this;
    }

    public function isEstPublique(): ?bool
    {
        return $this->est_publique;
    }

    public function setEstPublique(bool $est_publique): self
    {
        $this->est_publique = $est_publique;
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

    public function getEc(): ?Ec
    {
        return $this->ec;
    }

    public function setEc(?Ec $ec): self
    {
        $this->ec = $ec;
        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultValues(): void
    {
        if ($this->date_ajout === null) {
            $this->date_ajout = new \DateTime();
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}