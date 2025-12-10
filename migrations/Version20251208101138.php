<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208101138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route ADD start_lat DOUBLE PRECISION NOT NULL, ADD start_lng DOUBLE PRECISION NOT NULL, ADD end_lat DOUBLE PRECISION NOT NULL, ADD end_lng DOUBLE PRECISION NOT NULL, DROP start_location, DROP end_location, CHANGE distance distance INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route ADD start_location VARCHAR(100) NOT NULL, ADD end_location VARCHAR(100) NOT NULL, DROP start_lat, DROP start_lng, DROP end_lat, DROP end_lng, CHANGE distance distance INT NOT NULL');
    }
}
