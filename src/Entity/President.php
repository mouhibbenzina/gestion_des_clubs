<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class President extends User
{
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateElection = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $clubGere = null;

    public function getDateElection(): ?\DateTimeInterface { return $this->dateElection; }
    public function setDateElection(?\DateTimeInterface $v): static { $this->dateElection = $v; return $this; }

    public function getClubGere(): ?string { return $this->clubGere; }
    public function setClubGere(?string $v): static { $this->clubGere = $v; return $this; }
}