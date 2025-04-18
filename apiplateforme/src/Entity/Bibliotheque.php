<?php

namespace App\Entity;

use App\Repository\BibliothequeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"bibliotheque:read"}},
 *     denormalizationContext={"groups"={"bibliotheque:write"}},
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "controller"=App\Controller\BibliothequeUploadController::class,
 *             "deserialize"=false,
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
 *                                     "ec"={"type"="integer"}
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         }
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
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
     * @Assert\NotBlank
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"bibliotheque:read"})
     */
    private $fichier;

    /**
     * @Assert\File(maxSize="10M")
     * @Groups({"bibliotheque:write"})
     */
    private $file;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
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
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
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
     * @Groups({"bibliotheque:read", "bibliotheque:write"})
     * @Assert\NotNull
     */
    private $ec;

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

    public function setFichier(string $fichier): self
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

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}