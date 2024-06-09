<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606182828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE denuncia (id INT AUTO_INCREMENT NOT NULL, usuario_id INT DEFAULT NULL, receta_id INT DEFAULT NULL, motivo VARCHAR(255) NOT NULL, fecha DATE NOT NULL, INDEX IDX_F4236796DB38439E (usuario_id), INDEX IDX_F423679654F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE denuncia ADD CONSTRAINT FK_F4236796DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE denuncia ADD CONSTRAINT FK_F423679654F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE denuncia DROP FOREIGN KEY FK_F4236796DB38439E');
        $this->addSql('ALTER TABLE denuncia DROP FOREIGN KEY FK_F423679654F853F8');
        $this->addSql('DROP TABLE denuncia');
    }
}
