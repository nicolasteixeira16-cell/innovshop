<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025182209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_panier DROP FOREIGN KEY `FK_21691B4F347EFB`');
        $this->addSql('ALTER TABLE ligne_panier DROP FOREIGN KEY `FK_21691B4F77D927C`');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY `FK_24CC0DF2FB88E14F`');
        $this->addSql('DROP TABLE ligne_panier');
        $this->addSql('DROP TABLE panier');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ligne_panier (id INT AUTO_INCREMENT NOT NULL, date_ajout DATETIME NOT NULL, panier_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_21691B4F347EFB (produit_id), INDEX IDX_21691B4F77D927C (panier_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, utilisateur_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_24CC0DF2FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ligne_panier ADD CONSTRAINT `FK_21691B4F347EFB` FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ligne_panier ADD CONSTRAINT `FK_21691B4F77D927C` FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT `FK_24CC0DF2FB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }
}
