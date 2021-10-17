<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017105550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB96B3CA4B');
        $this->addSql('DROP INDEX IDX_169E6FB96B3CA4B ON course');
        $this->addSql('ALTER TABLE course CHANGE id_user user INT NOT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB98D93D649 FOREIGN KEY (user) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_169E6FB98D93D649 ON course (user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB98D93D649');
        $this->addSql('DROP INDEX IDX_169E6FB98D93D649 ON course');
        $this->addSql('ALTER TABLE course CHANGE user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB96B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_169E6FB96B3CA4B ON course (id_user)');
    }
}
