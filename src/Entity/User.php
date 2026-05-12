<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $matricule = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'user')]
    private Collection $candidatures;

    /**
     * @var Collection<int, Club>
     */
    #[ORM\OneToMany(targetEntity: Club::class, mappedBy: 'proposedBy')]
    private Collection $clubs;

    /**
     * @var Collection<int, ClubMember>
     */
    #[ORM\OneToMany(targetEntity: ClubMember::class, mappedBy: 'user')]
    private Collection $clubMembers;

    /**
     * @var Collection<int, Feedback>
     */
    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'user')]
    private Collection $feedbacks;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'user')]
    private Collection $participations;

    /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->clubs = new ArrayCollection();
        $this->clubMembers = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getMatricule(): ?string { return $this->matricule; }
    public function setMatricule(?string $matricule): static { $this->matricule = $matricule; return $this; }

    public function getCandidatures(): Collection { return $this->candidatures; }
    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setUser($this);
        }
        return $this;
    }
    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            if ($candidature->getUser() === $this) {
                $candidature->setUser(null);
            }
        }
        return $this;
    }

    public function getClubs(): Collection { return $this->clubs; }
    public function addClub(Club $club): static
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs->add($club);
            $club->setProposedBy($this);
        }
        return $this;
    }
    public function removeClub(Club $club): static
    {
        if ($this->clubs->removeElement($club)) {
            if ($club->getProposedBy() === $this) {
                $club->setProposedBy(null);
            }
        }
        return $this;
    }

    public function getClubMembers(): Collection { return $this->clubMembers; }
    public function addClubMember(ClubMember $clubMember): static
    {
        if (!$this->clubMembers->contains($clubMember)) {
            $this->clubMembers->add($clubMember);
            $clubMember->setUser($this);
        }
        return $this;
    }
    public function removeClubMember(ClubMember $clubMember): static
    {
        if ($this->clubMembers->removeElement($clubMember)) {
            if ($clubMember->getUser() === $this) {
                $clubMember->setUser(null);
            }
        }
        return $this;
    }

    public function getFeedbacks(): Collection { return $this->feedbacks; }
    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setUser($this);
        }
        return $this;
    }
    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            if ($feedback->getUser() === $this) {
                $feedback->setUser(null);
            }
        }
        return $this;
    }

    public function getParticipations(): Collection { return $this->participations; }
    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setUser($this);
        }
        return $this;
    }
    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            if ($participation->getUser() === $this) {
                $participation->setUser(null);
            }
        }
        return $this;
    }

    public function getReclamations(): Collection { return $this->reclamations; }
    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setUser($this);
        }
        return $this;
    }
    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getUser() === $this) {
                $reclamation->setUser(null);
            }
        }
        return $this;
    }

    public function eraseCredentials(): void {}

    public function isAdmin(): bool       { return in_array('ROLE_ADMIN', $this->roles); }
    public function isPresident(): bool   { return in_array('ROLE_PRESIDENT', $this->roles); }
    public function isResponsable(): bool { return in_array('ROLE_RESPONSABLE', $this->roles); }
    public function isEtudiant(): bool    { return !$this->isAdmin() && !$this->isPresident() && !$this->isResponsable(); }
}