<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240514162927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, estado VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comentario (id INT AUTO_INCREMENT NOT NULL, usuario_id INT DEFAULT NULL, receta_id INT DEFAULT NULL, comentario_id INT DEFAULT NULL, descripcion VARCHAR(255) NOT NULL, puntuacion INT NOT NULL, complejidad INT NOT NULL, INDEX IDX_4B91E702DB38439E (usuario_id), INDEX IDX_4B91E70254F853F8 (receta_id), INDEX IDX_4B91E702F3F2D7EC (comentario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imagen (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, imagen VARCHAR(255) NOT NULL, INDEX IDX_8319D2B354F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingrediente (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, cantidad INT NOT NULL, unidad VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lista (id INT AUTO_INCREMENT NOT NULL, usuario_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, descripcion VARCHAR(255) NOT NULL, imagen VARCHAR(255) NOT NULL, INDEX IDX_FB9FEEEDDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paso (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, numero INT NOT NULL, descripcion VARCHAR(255) NOT NULL, imagen VARCHAR(255) NOT NULL, INDEX IDX_DA71886B54F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE receta (id INT AUTO_INCREMENT NOT NULL, usuario_id INT DEFAULT NULL, tiempo INT NOT NULL, descripcion VARCHAR(255) NOT NULL, estado VARCHAR(255) NOT NULL, fecha DATE NOT NULL, nombre VARCHAR(255) NOT NULL, INDEX IDX_B093494EDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE receta_ingrediente (receta_id INT NOT NULL, ingrediente_id INT NOT NULL, INDEX IDX_F7A6A61354F853F8 (receta_id), INDEX IDX_F7A6A613769E458D (ingrediente_id), PRIMARY KEY(receta_id, ingrediente_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE receta_lista (receta_id INT NOT NULL, lista_id INT NOT NULL, INDEX IDX_7BBDE30F54F853F8 (receta_id), INDEX IDX_7BBDE30F6736D68F (lista_id), PRIMARY KEY(receta_id, lista_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE receta_categoria (receta_id INT NOT NULL, categoria_id INT NOT NULL, INDEX IDX_70B4CDCD54F853F8 (receta_id), INDEX IDX_70B4CDCD3397707A (categoria_id), PRIMARY KEY(receta_id, categoria_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, nombre_usuario VARCHAR(255) NOT NULL, imagen VARCHAR(255) NOT NULL, provincia VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_categoria (usuario_id INT NOT NULL, categoria_id INT NOT NULL, INDEX IDX_C72BF83FDB38439E (usuario_id), INDEX IDX_C72BF83F3397707A (categoria_id), PRIMARY KEY(usuario_id, categoria_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_usuario (usuario_source INT NOT NULL, usuario_target INT NOT NULL, INDEX IDX_5B431A1AA5989C7A (usuario_source), INDEX IDX_5B431A1ABC7DCCF5 (usuario_target), PRIMARY KEY(usuario_source, usuario_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E702DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E70254F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E702F3F2D7EC FOREIGN KEY (comentario_id) REFERENCES comentario (id)');
        $this->addSql('ALTER TABLE imagen ADD CONSTRAINT FK_8319D2B354F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
        $this->addSql('ALTER TABLE lista ADD CONSTRAINT FK_FB9FEEEDDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE paso ADD CONSTRAINT FK_DA71886B54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
        $this->addSql('ALTER TABLE receta ADD CONSTRAINT FK_B093494EDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE receta_ingrediente ADD CONSTRAINT FK_F7A6A61354F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE receta_ingrediente ADD CONSTRAINT FK_F7A6A613769E458D FOREIGN KEY (ingrediente_id) REFERENCES ingrediente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE receta_lista ADD CONSTRAINT FK_7BBDE30F54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE receta_lista ADD CONSTRAINT FK_7BBDE30F6736D68F FOREIGN KEY (lista_id) REFERENCES lista (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE receta_categoria ADD CONSTRAINT FK_70B4CDCD54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE receta_categoria ADD CONSTRAINT FK_70B4CDCD3397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_categoria ADD CONSTRAINT FK_C72BF83FDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_categoria ADD CONSTRAINT FK_C72BF83F3397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_usuario ADD CONSTRAINT FK_5B431A1AA5989C7A FOREIGN KEY (usuario_source) REFERENCES usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_usuario ADD CONSTRAINT FK_5B431A1ABC7DCCF5 FOREIGN KEY (usuario_target) REFERENCES usuario (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E702DB38439E');
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E70254F853F8');
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E702F3F2D7EC');
        $this->addSql('ALTER TABLE imagen DROP FOREIGN KEY FK_8319D2B354F853F8');
        $this->addSql('ALTER TABLE lista DROP FOREIGN KEY FK_FB9FEEEDDB38439E');
        $this->addSql('ALTER TABLE paso DROP FOREIGN KEY FK_DA71886B54F853F8');
        $this->addSql('ALTER TABLE receta DROP FOREIGN KEY FK_B093494EDB38439E');
        $this->addSql('ALTER TABLE receta_ingrediente DROP FOREIGN KEY FK_F7A6A61354F853F8');
        $this->addSql('ALTER TABLE receta_ingrediente DROP FOREIGN KEY FK_F7A6A613769E458D');
        $this->addSql('ALTER TABLE receta_lista DROP FOREIGN KEY FK_7BBDE30F54F853F8');
        $this->addSql('ALTER TABLE receta_lista DROP FOREIGN KEY FK_7BBDE30F6736D68F');
        $this->addSql('ALTER TABLE receta_categoria DROP FOREIGN KEY FK_70B4CDCD54F853F8');
        $this->addSql('ALTER TABLE receta_categoria DROP FOREIGN KEY FK_70B4CDCD3397707A');
        $this->addSql('ALTER TABLE usuario_categoria DROP FOREIGN KEY FK_C72BF83FDB38439E');
        $this->addSql('ALTER TABLE usuario_categoria DROP FOREIGN KEY FK_C72BF83F3397707A');
        $this->addSql('ALTER TABLE usuario_usuario DROP FOREIGN KEY FK_5B431A1AA5989C7A');
        $this->addSql('ALTER TABLE usuario_usuario DROP FOREIGN KEY FK_5B431A1ABC7DCCF5');
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE comentario');
        $this->addSql('DROP TABLE imagen');
        $this->addSql('DROP TABLE ingrediente');
        $this->addSql('DROP TABLE lista');
        $this->addSql('DROP TABLE paso');
        $this->addSql('DROP TABLE receta');
        $this->addSql('DROP TABLE receta_ingrediente');
        $this->addSql('DROP TABLE receta_lista');
        $this->addSql('DROP TABLE receta_categoria');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE usuario_categoria');
        $this->addSql('DROP TABLE usuario_usuario');
    }
}
