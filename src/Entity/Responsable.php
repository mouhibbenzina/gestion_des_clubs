<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Responsable extends User
{
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $grade = null;

    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $v): static { $this->departement = $v; return $this; }

    public function getGrade(): ?string { return $this->grade; }
    public function setGrade(?string $v): static { $this->grade = $v; return $this; }
}