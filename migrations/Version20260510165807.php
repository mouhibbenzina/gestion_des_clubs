<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510165807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, cv_filename VARCHAR(255) DEFAULT NULL, message LONGTEXT DEFAULT NULL, status VARCHAR(30) NOT NULL, submitted_at DATETIME NOT NULL, user_id INT NOT NULL, recrutement_id INT NOT NULL, INDEX IDX_E33BD3B8A76ED395 (user_id), INDEX IDX_E33BD3B8FCC7117B (recrutement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, domain VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, proposed_by_id INT NOT NULL, INDEX IDX_B8EE3872DAB5A938 (proposed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club_member (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(100) NOT NULL, joined_at DATETIME NOT NULL, user_id INT NOT NULL, club_id INT NOT NULL, INDEX IDX_552B46F2A76ED395 (user_id), INDEX IDX_552B46F261190A32 (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom_evenement VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, lieu VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, club_id INT NOT NULL, INDEX IDX_B26681E61190A32 (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, rating INT DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX IDX_D2294458A76ED395 (user_id), INDEX IDX_D2294458FD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, registered_at DATETIME NOT NULL, presence_status VARCHAR(50) NOT NULL, feedback LONGTEXT DEFAULT NULL, user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX IDX_AB55E24FA76ED395 (user_id), INDEX IDX_AB55E24FFD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_CE606404A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recrutement (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, requirements LONGTEXT DEFAULT NULL, deadline DATE DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, club_id INT NOT NULL, INDEX IDX_25EB231961190A32 (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, matricule VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8FCC7117B FOREIGN KEY (recrutement_id) REFERENCES recrutement (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872DAB5A938 FOREIGN KEY (proposed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE club_member ADD CONSTRAINT FK_552B46F2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE club_member ADD CONSTRAINT FK_552B46F261190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE recrutement ADD CONSTRAINT FK_25EB231961190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8A76ED395');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8FCC7117B');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872DAB5A938');
        $this->addSql('ALTER TABLE club_member DROP FOREIGN KEY FK_552B46F2A76ED395');
        $this->addSql('ALTER TABLE club_member DROP FOREIGN KEY FK_552B46F261190A32');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E61190A32');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458FD02F13');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FA76ED395');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FFD02F13');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('ALTER TABLE recrutement DROP FOREIGN KEY FK_25EB231961190A32');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_member');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE recrutement');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
