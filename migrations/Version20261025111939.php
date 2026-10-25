<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025111939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD statut VARCHAR(20) DEFAULT \'publie\' NOT NULL, ADD vendeur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27858C065E FOREIGN KEY (vendeur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_produit_statut ON produit (statut)');
        $this->addSql('CREATE INDEX IDX_29A5EC27858C065E ON produit (vendeur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27858C065E');
        $this->addSql('DROP INDEX idx_produit_statut ON produit');
        $this->addSql('DROP INDEX IDX_29A5EC27858C065E ON produit');
        $this->addSql('ALTER TABLE produit DROP statut, DROP vendeur_id');
    }
}
