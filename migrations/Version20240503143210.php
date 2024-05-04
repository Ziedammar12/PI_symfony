<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503143210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, prix_unitaire INT NOT NULL, quantite INT NOT NULL, montant_ht DOUBLE PRECISION NOT NULL, tva DOUBLE PRECISION NOT NULL, montant_ttc DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, type_devise VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nb_produit INT NOT NULL, photo VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix INT NOT NULL, name VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_facture (produit_id INT NOT NULL, facture_id INT NOT NULL, INDEX IDX_387DEF69F347EFB (produit_id), INDEX IDX_387DEF697F2DEE08 (facture_id), PRIMARY KEY(produit_id, facture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit_facture ADD CONSTRAINT FK_387DEF69F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_facture ADD CONSTRAINT FK_387DEF697F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit_facture DROP FOREIGN KEY FK_387DEF69F347EFB');
        $this->addSql('ALTER TABLE produit_facture DROP FOREIGN KEY FK_387DEF697F2DEE08');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE produit_facture');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
