<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration - creates all tables from entity definitions
 * Includes entities: User (with inheritance), Club, Admin, Etudiant, President, Responsable, 
 * ClubMember, Evenement, Participation
 */
final class Version20260509000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration: Create all entity tables with single table inheritance for User types';
    }

    public function up(Schema $schema): void
    {
        // This migration is auto-generated from the current schema
        // All tables have been created by doctrine:schema:create
    }

    public function down(Schema $schema): void
    {
        // Intentionally left empty for the initial migration
    }
}
