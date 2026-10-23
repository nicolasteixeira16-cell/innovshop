<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261023142632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_commande_statut ON commande (statut)');
        $this->addSql('CREATE INDEX idx_commande_date_creation ON commande (date_creation)');
        $this->addSql('CREATE INDEX idx_produit_date_creation ON produit (date_creation)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_commande_statut ON commande');
        $this->addSql('DROP INDEX idx_commande_date_creation ON commande');
        $this->addSql('DROP INDEX idx_produit_date_creation ON produit');
    }
}
