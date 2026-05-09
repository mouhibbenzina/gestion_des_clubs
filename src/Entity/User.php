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
#[ORM\InheritanceType('SINGLE_TABLE')]          // ← Tout dans UNE seule table
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')] // ← colonne discriminante
#[ORM\DiscriminatorMap([
    'admin'       => Admin::class,
    'etudiant'    => Etudiant::class,
    'president'   => President::class,
    'responsable' => Responsable::class,
])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
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

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $participations;

    /**
     * @var Collection<int, ClubMember>
     */
    #[ORM\OneToMany(targetEntity: ClubMember::class, mappedBy: 'user')]
    private Collection $clubMembers;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->clubMembers = new ArrayCollection();
    }

    // ── Getters / Setters ──────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // garanti pour tous
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function eraseCredentials(): void {}

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

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
            // set the owning side to null (unless already changed)
            if ($participation->getUser() === $this) {
                $participation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClubMember>
     */
    public function getClubMembers(): Collection
    {
        return $this->clubMembers;
    }

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
            // set the owning side to null (unless already changed)
            if ($clubMember->getUser() === $this) {
                $clubMember->setUser(null);
            }
        }

        return $this;
    }
}