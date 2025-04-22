<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
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
 *         "order"={"created_at": "DESC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"document:read"}},
 *     denormalizationContext={"groups"={"document:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "method"="POST",
 *             "path"="/documents/upload",
 *             "controller"=App\Controller\DocumentUploadController::class,
 *             "deserialize"=false,
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
 *                                     "type_document"={"type"="string"}
 *                                 },
 *                                 "required"={"file", "titre", "type_document"}
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "titre": "partial",
 *     "type_document": "exact",
 *     "user.id": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "titre", "created_at"})
 * @ApiFilter(BooleanFilter::class, properties={"est_valide"})
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"document:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"document:read", "document:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"document:read"})
     */
    private $filePath;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"document:read", "document:write"})
     * @Assert\NotBlank
     * @Assert\Choice({"cours", "td", "tp", "projet", "autre"})
     */
    private $type_document;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"document:read", "document:write"})
     */
    private $est_valide;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"document:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"document:read"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"document:read"})
     */
    private $user;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    // Getters et Setters

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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getTypeDocument(): ?string
    {
        return $this->type_document;
    }

    public function setTypeDocument(string $type_document): self
    {
        $this->type_document = $type_document;
        return $this;
    }

    public function isEstValide(): ?bool
    {
        return $this->est_valide;
    }

    public function setEstValide(?bool $est_valide): self
    {
        $this->est_valide = $est_valide;
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

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}