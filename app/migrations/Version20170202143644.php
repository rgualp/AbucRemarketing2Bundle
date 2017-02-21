<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170202143644 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE override_user (override_id INT AUTO_INCREMENT NOT NULL, override_to INT NOT NULL, override_by INT NOT NULL,override_date DATETIME NOT NULL, reason VARCHAR(400) DEFAULT NULL,override_enable TINYINT(1) NOT NULL,override_password VARCHAR(255) NOT NULL, INDEX IDX_B6BD307FEBA5B1E4 (override_to), INDEX IDX_B6BD307F6827D7C1 (override_by), PRIMARY KEY(override_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE override_user ADD CONSTRAINT FK_B6BD307FEBA5B1E4 FOREIGN KEY (override_to) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE override_user ADD CONSTRAINT FK_B6BD307F6827D7C1 FOREIGN KEY (override_by) REFERENCES user (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
