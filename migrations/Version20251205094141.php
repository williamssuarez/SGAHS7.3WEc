<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205094141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alergias CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE ciudad CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE discapacidades CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE empleado CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE enfermedades CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE medicamentos CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE paciente CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE tratamientos CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE status_id status_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alergias CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ciudad CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE discapacidades CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE empleado CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enfermedades CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE medicamentos CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paciente CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tratamientos CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE status_id status_id INT DEFAULT NULL');
    }
}
