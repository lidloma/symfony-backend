<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240515070424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE usuario_comentario (usuario_id INT NOT NULL, comentario_id INT NOT NULL, INDEX IDX_B8A1ADA0DB38439E (usuario_id), INDEX IDX_B8A1ADA0F3F2D7EC (comentario_id), PRIMARY KEY(usuario_id, comentario_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_receta (usuario_id INT NOT NULL, receta_id INT NOT NULL, INDEX IDX_4A81AA47DB38439E (usuario_id), INDEX IDX_4A81AA4754F853F8 (receta_id), PRIMARY KEY(usuario_id, receta_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario_comentario ADD CONSTRAINT FK_B8A1ADA0DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_comentario ADD CONSTRAINT FK_B8A1ADA0F3F2D7EC FOREIGN KEY (comentario_id) REFERENCES comentario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_receta ADD CONSTRAINT FK_4A81AA47DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_receta ADD CONSTRAINT FK_4A81AA4754F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuario_comentario DROP FOREIGN KEY FK_B8A1ADA0DB38439E');
        $this->addSql('ALTER TABLE usuario_comentario DROP FOREIGN KEY FK_B8A1ADA0F3F2D7EC');
        $this->addSql('ALTER TABLE usuario_receta DROP FOREIGN KEY FK_4A81AA47DB38439E');
        $this->addSql('ALTER TABLE usuario_receta DROP FOREIGN KEY FK_4A81AA4754F853F8');
        $this->addSql('DROP TABLE usuario_comentario');
        $this->addSql('DROP TABLE usuario_receta');
    }
}
