<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Etudiant extends User
{
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $filiere = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeEtude = null;

    public function getMatricule(): ?string { return $this->matricule; }
    public function setMatricule(?string $v): static { $this->matricule = $v; return $this; }

    public function getFiliere(): ?string { return $this->filiere; }
    public function setFiliere(?string $v): static { $this->filiere = $v; return $this; }

    public function getAnneeEtude(): ?int { return $this->anneeEtude; }
    public function setAnneeEtude(?int $v): static { $this->anneeEtude = $v; return $this; }
}