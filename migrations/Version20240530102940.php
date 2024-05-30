<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240530102940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categoria CHANGE imagen imagen LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE lista CHANGE imagen imagen LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE paso CHANGE imagen imagen LONGBLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paso CHANGE imagen imagen VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE lista CHANGE imagen imagen VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE categoria CHANGE imagen imagen VARCHAR(255) NOT NULL');
    }
}
