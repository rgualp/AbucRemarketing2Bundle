<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160607180632 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownershipstatistics (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, user INT DEFAULT NULL, status INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, date DATETIME NOT NULL, created TINYINT(1) NOT NULL, INDEX IDX_8D20B7162D385412 (accommodation), INDEX IDX_8D20B7168D93D649 (user), INDEX IDX_8D20B7167B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownershipstatistics ADD CONSTRAINT FK_8D20B7162D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownershipstatistics ADD CONSTRAINT FK_8D20B7168D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE ownershipstatistics ADD CONSTRAINT FK_8D20B7167B00651C FOREIGN KEY (status) REFERENCES ownershipstatus (status_id)');
        $this->addSql('ALTER TABLE ownership ADD waiting_for_revision TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownershipstatistics');
        $this->addSql('ALTER TABLE ownership DROP waiting_for_revision');
    }
}
