<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161125211534 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE penalty (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, accommodation INT DEFAULT NULL, description LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, finalizationDate DATETIME NOT NULL, INDEX IDX_AFE28FD88D93D649 (user), INDEX IDX_AFE28FD82D385412 (accommodation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE penalty ADD CONSTRAINT FK_AFE28FD88D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE penalty ADD CONSTRAINT FK_AFE28FD82D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE penalty');
    }
}
