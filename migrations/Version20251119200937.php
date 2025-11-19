<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119200937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ciudad (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, poblacion INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE empleado ADD ciudad_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE empleado ADD CONSTRAINT FK_D9D9BF52E8608214 FOREIGN KEY (ciudad_id) REFERENCES ciudad (id)');
        $this->addSql('CREATE INDEX IDX_D9D9BF52E8608214 ON empleado (ciudad_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empleado DROP FOREIGN KEY FK_D9D9BF52E8608214');
        $this->addSql('DROP TABLE ciudad');
        $this->addSql('DROP INDEX IDX_D9D9BF52E8608214 ON empleado');
        $this->addSql('ALTER TABLE empleado DROP ciudad_id');
    }
}
