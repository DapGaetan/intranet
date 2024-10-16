<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016135508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cultural_event_ticket (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, created_by_id INT NOT NULL, logo VARCHAR(255) NOT NULL, season VARCHAR(255) NOT NULL, series VARCHAR(70) NOT NULL, placing VARCHAR(60) NOT NULL, siret VARCHAR(14) NOT NULL, licence VARCHAR(60) NOT NULL, background VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_91D9A723AE80F5DF (department_id), INDEX IDX_91D9A723B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cultural_event_ticket ADD CONSTRAINT FK_91D9A723AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE cultural_event_ticket ADD CONSTRAINT FK_91D9A723B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cultural_event_ticket DROP FOREIGN KEY FK_91D9A723AE80F5DF');
        $this->addSql('ALTER TABLE cultural_event_ticket DROP FOREIGN KEY FK_91D9A723B03A8386');
        $this->addSql('DROP TABLE cultural_event_ticket');
    }
}
