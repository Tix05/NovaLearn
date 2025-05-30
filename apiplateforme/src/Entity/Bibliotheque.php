<?php

namespace App\Entity;

use App\Repository\BibliothequeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"bibliotheque:read", "bibliotheque:list"}},
 *     denormalizationContext={"groups"={"bibliotheque:write"}},
 *     collectionOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"bibliotheque:list"}}
 *         },
 *         "post"={
 *             "method"="POST",
 *             "path"="/bibliotheques",
 *             "security"="is_granted('ROLE_USER')",
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
 *                                     "parcours"={"type"="string"}
 *                                 },
 *                                 "required"={"file", "titre", "type", "ec", "parcours"}
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "patch"={
 *             "method"="PATCH",
 *             "path"="/bibliotheques/{id}",
 *             "security"="is_granted('ROLE_ADMIN') or object.getUser() == user",
 *             "openapi_context"={
 *                 "requestBody"={
 *                     "content"={
 *                         "application/json"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "titre"={"type"="string"},
 *                                     "type"={"type"="string"},
 *                                     "ec"={"type"="integer"},
 *                                     "parcours"={"type"="string"},
 *                                     "status"={"type"="boolean"}
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         },
 *         "upload_file"={
 *             "method"="POST",
 *             "path"="/bibliotheques/{id}/upload",
 *             "controller"=BibliothequeController::class,
 *             "security"="is_granted('ROLE_ADMIN') or object.getUser() == user",
 *             "openapi_context"={
 *                 "requestBody"={
 *                     "content"={
 *                         "multipart/form-data"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "file"={"type"="string", "format"="binary"}
 *                                 },
 *                                 "required"={"file"}
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         },
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "titre": "partial",
 *     "type": "exact",
 *     "ec.nom": "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "titre", "createdAt"})
 * @ORM\Entity(repositoryClass=BibliothequeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Bibliotheque
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"bibliotheque:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"bibliotheque:read", "bibliotheque:write", "bibliotheque:list"})
     * @Assert\NotBlank
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    private $fichier;

    /**
     * @Vich\UploadableField(mapping="bibliotheque_files", fileNameProperty="fichier")
     * @Assert\File(
     *     maxSize="10M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/msword",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
     *     }
     * )
     * @Groups({"bibliotheque:write"})
     */
    private ?File $file = null;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"bibliotheque:read", "bibliotheque:write", "bibliotheque:list"})
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"bibliotheque:read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"bibliotheque:read"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="bibliotheques")
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="bibliotheques")
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=Ec::class, inversedBy="bibliotheques")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"bibliotheque:read", "bibliotheque:write", "bibliotheque:list"})
     */
    private $ec;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bibliotheques")
     * @Groups({"bibliotheque:read"})
     */
    private $user;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): self
    {
        $this->fichier = $fichier;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;
        if ($file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
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
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    public function getMentionName(): ?string
    {
        return $this->mention?->getName();
    }

    /**
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    public function getNiveauNom(): ?string
    {
        return $this->parcours?->getNiveau()?->getNom();
    }

    /**
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    public function getEcName(): ?string
    {
        return $this->ec?->getName();
    }

    /**
     * @Groups({"bibliotheque:read", "bibliotheque:list"})
     */
    public function getParcoursName(): ?string
    {
        return $this->parcours?->getName();
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}