<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210827124844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inspiration (id INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, title VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, trash TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_FDEC44402B36786B (title), INDEX IDX_FDEC44406B3CA4B (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parameters (id INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, view_counter TINYINT(1) NOT NULL, email_notifications TINYINT(1) NOT NULL, INDEX IDX_69348FE6B3CA4B (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE views (id INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_story INT DEFAULT NULL, commentary TEXT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_11F09C876B3CA4B (id_user), INDEX IDX_11F09C875D34508 (id_story), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspiration ADD CONSTRAINT FK_FDEC44406B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE parameters ADD CONSTRAINT FK_69348FE6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE views ADD CONSTRAINT FK_11F09C876B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE views ADD CONSTRAINT FK_11F09C875D34508 FOREIGN KEY (id_story) REFERENCES inspiration (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE views DROP FOREIGN KEY FK_11F09C875D34508');
        $this->addSql('DROP TABLE inspiration');
        $this->addSql('DROP TABLE parameters');
        $this->addSql('DROP TABLE views');
    }
}
