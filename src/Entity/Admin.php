<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Admin extends User
{
    // Colonnes spécifiques à l'admin (nullable car SINGLE_TABLE)
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveauAcces = null;

    public function getNiveauAcces(): ?string { return $this->niveauAcces; }
    public function setNiveauAcces(?string $niveauAcces): static
    {
        $this->niveauAcces = $niveauAcces;
        return $this;
    }
}