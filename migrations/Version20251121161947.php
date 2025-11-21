<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121161947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paciente_enfermedades (paciente_id INT NOT NULL, enfermedades_id INT NOT NULL, INDEX IDX_95976AF47310DAD4 (paciente_id), INDEX IDX_95976AF43035D395 (enfermedades_id), PRIMARY KEY(paciente_id, enfermedades_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paciente_alergias (paciente_id INT NOT NULL, alergias_id INT NOT NULL, INDEX IDX_92BA24157310DAD4 (paciente_id), INDEX IDX_92BA2415ECB52FB8 (alergias_id), PRIMARY KEY(paciente_id, alergias_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paciente_discapacidades (paciente_id INT NOT NULL, discapacidades_id INT NOT NULL, INDEX IDX_7B392C047310DAD4 (paciente_id), INDEX IDX_7B392C043D9D1468 (discapacidades_id), PRIMARY KEY(paciente_id, discapacidades_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paciente_tratamientos (paciente_id INT NOT NULL, tratamientos_id INT NOT NULL, INDEX IDX_F99D548F7310DAD4 (paciente_id), INDEX IDX_F99D548FC981A62B (tratamientos_id), PRIMARY KEY(paciente_id, tratamientos_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paciente_enfermedades ADD CONSTRAINT FK_95976AF47310DAD4 FOREIGN KEY (paciente_id) REFERENCES paciente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_enfermedades ADD CONSTRAINT FK_95976AF43035D395 FOREIGN KEY (enfermedades_id) REFERENCES enfermedades (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_alergias ADD CONSTRAINT FK_92BA24157310DAD4 FOREIGN KEY (paciente_id) REFERENCES paciente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_alergias ADD CONSTRAINT FK_92BA2415ECB52FB8 FOREIGN KEY (alergias_id) REFERENCES alergias (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_discapacidades ADD CONSTRAINT FK_7B392C047310DAD4 FOREIGN KEY (paciente_id) REFERENCES paciente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_discapacidades ADD CONSTRAINT FK_7B392C043D9D1468 FOREIGN KEY (discapacidades_id) REFERENCES discapacidades (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_tratamientos ADD CONSTRAINT FK_F99D548F7310DAD4 FOREIGN KEY (paciente_id) REFERENCES paciente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paciente_tratamientos ADD CONSTRAINT FK_F99D548FC981A62B FOREIGN KEY (tratamientos_id) REFERENCES tratamientos (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paciente_enfermedades DROP FOREIGN KEY FK_95976AF47310DAD4');
        $this->addSql('ALTER TABLE paciente_enfermedades DROP FOREIGN KEY FK_95976AF43035D395');
        $this->addSql('ALTER TABLE paciente_alergias DROP FOREIGN KEY FK_92BA24157310DAD4');
        $this->addSql('ALTER TABLE paciente_alergias DROP FOREIGN KEY FK_92BA2415ECB52FB8');
        $this->addSql('ALTER TABLE paciente_discapacidades DROP FOREIGN KEY FK_7B392C047310DAD4');
        $this->addSql('ALTER TABLE paciente_discapacidades DROP FOREIGN KEY FK_7B392C043D9D1468');
        $this->addSql('ALTER TABLE paciente_tratamientos DROP FOREIGN KEY FK_F99D548F7310DAD4');
        $this->addSql('ALTER TABLE paciente_tratamientos DROP FOREIGN KEY FK_F99D548FC981A62B');
        $this->addSql('DROP TABLE paciente_enfermedades');
        $this->addSql('DROP TABLE paciente_alergias');
        $this->addSql('DROP TABLE paciente_discapacidades');
        $this->addSql('DROP TABLE paciente_tratamientos');
    }
}
