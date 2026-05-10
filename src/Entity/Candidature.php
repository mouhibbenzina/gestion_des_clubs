<?php
namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvFilename = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 30)]
    private ?string $status = 'pending';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $submittedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Recrutement::class, inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recrutement $recrutement = null;

    public function __construct()
    {
        $this->submittedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getCvFilename(): ?string { return $this->cvFilename; }
    public function setCvFilename(?string $cvFilename): static { $this->cvFilename = $cvFilename; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): static { $this->message = $message; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getSubmittedAt(): ?\DateTimeInterface { return $this->submittedAt; }
    public function setSubmittedAt(\DateTimeInterface $submittedAt): static { $this->submittedAt = $submittedAt; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getRecrutement(): ?Recrutement { return $this->recrutement; }
    public function setRecrutement(?Recrutement $recrutement): static { $this->recrutement = $recrutement; return $this; }
}