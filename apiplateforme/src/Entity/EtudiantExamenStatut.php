<?php

namespace App\Entity;

use App\Repository\EtudiantExamenStatutRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"etudiant_examen_statut:read"}},
 *     denormalizationContext={"groups"={"etudiant_examen_statut:write"}},
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
 *     "etudiant.id": "exact",
 *     "examen.id": "exact",
 *     "statut": "exact"
 * })
 * @ORM\Entity(repositoryClass=EtudiantExamenStatutRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class EtudiantExamenStatut
{
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_SOUMIS = 'SOUMIS';
    public const STATUT_ABANDONNE = 'ABANDONNE';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"etudiant_examen_statut:read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class, inversedBy="etudiantExamenStatuts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
     * @Assert\NotNull
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=Examen::class, inversedBy="etudiantExamenStatuts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
     * @Assert\NotNull
     */
    private $examen;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices={
     *         EtudiantExamenStatut::STATUT_EN_COURS,
     *         EtudiantExamenStatut::STATUT_SOUMIS,
     *         EtudiantExamenStatut::STATUT_ABANDONNE
     *     },
     *     message="Choisissez un statut valide: EN_COURS, SOUMIS ou ABANDONNE"
     * )
     */
    private $statut = self::STATUT_EN_COURS;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
     */
    private $debutExamen;

   /**
 * @ORM\Column(type="integer")
 * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
 * @Assert\PositiveOrZero
 */
private int $tempsRestant;


    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"etudiant_examen_statut:read", "etudiant_examen_statut:write"})
     */
   private ?array $reponses = null;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"etudiant_examen_statut:read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"etudiant_examen_statut:read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"etudiant_examen_statut:read"})
     */
    private $derniereActivite;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->derniereActivite = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    public function getExamen(): ?Examen
    {
        return $this->examen;
    }

    public function setExamen(?Examen $examen): self
    {
        $this->examen = $examen;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDebutExamen(): ?\DateTimeInterface
    {
        return $this->debutExamen;
    }

    public function setDebutExamen(?\DateTimeInterface $debutExamen): self
    {
        $this->debutExamen = $debutExamen;

        return $this;
    }

    public function getTempsRestant(): ?int
    {
        return $this->tempsRestant;
    }

    public function setTempsRestant(int $tempsRestant): self
{
    // Validation stricte - n'accepte plus null
    $this->tempsRestant = max(0, $tempsRestant);
    $this->updateDerniereActivite();
    
    return $this;
}

    public function getReponses(): ?array
    {
        return $this->reponses;
    }

    public function setReponses($reponses): self
    {
        if (is_string($reponses)) {
            $this->reponses = json_decode($reponses, true);
        } else {
            $this->reponses = $reponses;
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDerniereActivite(): ?\DateTimeInterface
    {
        return $this->derniereActivite;
    }

    public function setDerniereActivite(\DateTimeInterface $derniereActivite): self
    {
        $this->derniereActivite = $derniereActivite;

        return $this;
    }

public function calculerTempsRestant(int $dureeExamen): int
{
    if ($this->statut !== self::STATUT_EN_COURS) {
        $this->tempsRestant = 0;
        return 0;
    }

    if (!$this->debutExamen) {
        $this->tempsRestant = $dureeExamen;
        $this->updateDerniereActivite();
        return $dureeExamen;
    }

    $now = new \DateTime();
    $tempsEcoule = $now->getTimestamp() - $this->debutExamen->getTimestamp();
    $tempsRestant = max(0, $dureeExamen - $tempsEcoule);

    if ($tempsRestant <= 0) {
        $this->logger->warning('Temps restant calculé à 0 pour examen en cours', [
            'examen_id' => $this->examen->getId(),
            'etudiant_id' => $this->etudiant->getId(),
            'debut_examen' => $this->debutExamen->format('Y-m-d H:i:s'),
            'duree_examen' => $dureeExamen
        ]);
        $this->setStatut(self::STATUT_SOUMIS);
        $this->tempsRestant = 0;
    } else {
        $this->tempsRestant = $tempsRestant;
    }

    $this->updateDerniereActivite();
    return $this->tempsRestant;
}

public function initialiserTempsRestant(int $dureeExamen): void
{
    if ($this->statut !== self::STATUT_EN_COURS) {
        $this->tempsRestant = 0;
        return;
    }

    if (!$this->debutExamen) {
        $this->debutExamen = new \DateTime();
        $this->tempsRestant = $dureeExamen;
        $this->updateDerniereActivite();
        return;
    }

    $now = new \DateTime();
    $tempsEcoule = $now->getTimestamp() - $this->debutExamen->getTimestamp();
    $this->tempsRestant = max(0, $dureeExamen - $tempsEcoule);
    $this->updateDerniereActivite();
}


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDerniereActivite(): void
    {
        $this->derniereActivite = new \DateTime();
    }
}