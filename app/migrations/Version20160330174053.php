<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160330174053 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, status INT DEFAULT NULL, sendTo VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, subtype VARCHAR(255) NOT NULL, notificationType INT DEFAULT NULL, INDEX IDX_BF5476CAE069D4DA (notificationType), INDEX IDX_BF5476CA7B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE069D4DA FOREIGN KEY (notificationType) REFERENCES nomenclator (nom_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7B00651C FOREIGN KEY (status) REFERENCES nomenclator (nom_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE notification');
    }
}
