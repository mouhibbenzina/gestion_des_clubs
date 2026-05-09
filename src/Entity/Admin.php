<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin extends User
{
    /**
     * Niveau d'accès : 1 = super admin, 2 = admin standard
     */
    #[ORM\Column(type: 'integer', options: ['default' => 2])]
    #[Assert\Range(min: 1, max: 2)]
    private int $niveauAcces = 2;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $poste = null;

    public function __construct()
    {
        parent::__construct();
        $this->setRoles(['ROLE_ADMIN']);
    }

    public function getType(): string
    {
        return 'admin';
    }

    public function getNiveauAcces(): int
    {
        return $this->niveauAcces;
    }

    public function setNiveauAcces(int $niveauAcces): static
    {
        $this->niveauAcces = $niveauAcces;

        return $this;
    }

    public function isSuperAdmin(): bool
    {
        return $this->niveauAcces === 1;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): static
    {
        $this->poste = $poste;

        return $this;
    }
}