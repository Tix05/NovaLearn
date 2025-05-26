<?php

namespace App\Entity;

use App\Repository\NiveauRepository;
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
 *         "order"={"ordre": "ASC"},
 *         "pagination_client_items_per_page"=true
 *     },
 *     normalizationContext={"groups"={"niveau:read"}},
 *     denormalizationContext={"groups"={"niveau:write"}},
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
 *     "nom": "partial",
 *     "cycle": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "code", "nom", "ordre"})
 * @ORM\Entity(repositoryClass=NiveauRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Niveau
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"niveau:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     * @Groups({"niveau:read", "niveau:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=10)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"niveau:read", "niveau:write"})
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=2)
     * @Groups({"niveau:read", "niveau:write"})
     * @Assert\NotBlank
     * @Assert\Length(min=1, max=2)
     * @Assert\Choice({"L", "M", "D"}) // Licence, Master, Doctorat
     */
    private $cycle;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"niveau:read", "niveau:write"})
     * @Assert\NotBlank
     * @Assert\Positive
     */
    private $ordre;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"niveau:read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"niveau:read"})
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="niveau")
     * @Groups({"niveau:read"})
     */
    private $etudiants;

    // /**
    //  * @ORM\OneToMany(targetEntity=Ue::class, mappedBy="niveau")
    //  * @Groups({"niveau:read"})
    //  */
    // private $ues;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="niveau")
     * @Groups({"niveau:read"})
     */
    private $agendas;

    /**
     * @ORM\OneToMany(targetEntity=NotificationGroupe::class, mappedBy="niveau")
     * @Groups({"niveau:read"})
     */
    private $notificationGroupes;

    /**
     * @ORM\OneToMany(targetEntity=Semestre::class, mappedBy="niveau", orphanRemoval=true)
     */
    private Collection $semestres;


    public function __construct()
    {
        $this->etudiants = new ArrayCollection();
        $this->semestres = new ArrayCollection();
        $this->ues = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->notificationGroupes = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCycle(): ?string
    {
        return $this->cycle;
    }

    public function setCycle(string $cycle): self
    {
        $this->cycle = $cycle;
        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
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
            $etudiant->setNiveau($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getNiveau() === $this) {
                $etudiant->setNiveau(null);
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
            $ue->setNiveau($this);
        }

        return $this;
    }

    public function removeUe(Ue $ue): self
    {
        if ($this->ues->removeElement($ue)) {
            // set the owning side to null (unless already changed)
            if ($ue->getNiveau() === $this) {
                $ue->setNiveau(null);
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
            $agenda->setNiveau($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getNiveau() === $this) {
                $agenda->setNiveau(null);
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
            $notificationGroupe->setNiveau($this);
        }

        return $this;
    }

    public function removeNotificationGroupe(NotificationGroupe $notificationGroupe): static
    {
        if ($this->notificationGroupes->removeElement($notificationGroupe)) {
            // set the owning side to null (unless already changed)
            if ($notificationGroupe->getNiveau() === $this) {
                $notificationGroupe->setNiveau(null);
            }
        }

        return $this;
    }

    /**
 * @return Collection<int, Semestre>
 */
public function getSemestres(): Collection
{
    return $this->semestres;
}

public function addSemestre(Semestre $semestre): self
{
    if (!$this->semestres->contains($semestre)) {
        $this->semestres->add($semestre);
        $semestre->setNiveau($this);
    }
    return $this;
}

public function removeSemestre(Semestre $semestre): self
{
    if ($this->semestres->removeElement($semestre)) {
        if ($semestre->getNiveau() === $this) {
            $semestre->setNiveau(null);
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
        return $this->code . ' - ' . $this->nom;
    }
}
