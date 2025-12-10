<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117222148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignment (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, driver_id INT NOT NULL, start_date VARCHAR(10) NOT NULL, end_date VARCHAR(10) NOT NULL, INDEX IDX_30C544BA545317D1 (vehicle_id), INDEX IDX_30C544BAC3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE driver (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, license_category VARCHAR(10) NOT NULL, contact VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maintenance_record (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, date VARCHAR(10) NOT NULL, work_type VARCHAR(255) NOT NULL, cost DOUBLE PRECISION NOT NULL, INDEX IDX_B1C9A998545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refuel (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, date VARCHAR(10) NOT NULL, liters DOUBLE PRECISION NOT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_B60345A1545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, start_location VARCHAR(100) NOT NULL, end_location VARCHAR(100) NOT NULL, distance INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trip (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, driver_id INT DEFAULT NULL, date VARCHAR(10) NOT NULL, kilometers INT NOT NULL, fuel_used DOUBLE PRECISION NOT NULL, INDEX IDX_7656F53B545317D1 (vehicle_id), INDEX IDX_7656F53BC3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, vin VARCHAR(17) NOT NULL, plate_number VARCHAR(20) NOT NULL, model VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, mileage INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BAC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE maintenance_record ADD CONSTRAINT FK_B1C9A998545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE refuel ADD CONSTRAINT FK_B60345A1545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BA545317D1');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BAC3423909');
        $this->addSql('ALTER TABLE maintenance_record DROP FOREIGN KEY FK_B1C9A998545317D1');
        $this->addSql('ALTER TABLE refuel DROP FOREIGN KEY FK_B60345A1545317D1');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B545317D1');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BC3423909');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE maintenance_record');
        $this->addSql('DROP TABLE refuel');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE trip');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
