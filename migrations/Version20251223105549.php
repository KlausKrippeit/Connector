<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223105549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE podcast_episode (id INT AUTO_INCREMENT NOT NULL, guid VARCHAR(64) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', episode_url VARCHAR(255) NOT NULL, audio_url VARCHAR(255) NOT NULL, audio_size BIGINT NOT NULL, audio_type VARCHAR(64) NOT NULL, local_path VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, UNIQUE INDEX uniq_guid (guid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE podcast_episode');
    }
}
