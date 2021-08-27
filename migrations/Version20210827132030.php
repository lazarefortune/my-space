<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210827132030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentary_story (id INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, story_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, trash TINYINT(1) NOT NULL, INDEX IDX_D9BCED3D6B3CA4B (id_user), INDEX IDX_D9BCED3DAA5D4036 (story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentary_story ADD CONSTRAINT FK_D9BCED3D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE commentary_story ADD CONSTRAINT FK_D9BCED3DAA5D4036 FOREIGN KEY (story_id) REFERENCES inspiration (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commentary_story');
    }
}
