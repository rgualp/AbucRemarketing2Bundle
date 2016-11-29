<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161128173449 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tourist_failure (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, accommodation INT DEFAULT NULL, reservation INT DEFAULT NULL, description LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_45496E218D93D649 (user), INDEX IDX_45496E212D385412 (accommodation), UNIQUE INDEX UNIQ_45496E2142C84955 (reservation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E218D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E212D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E2142C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tourist_failure');
    }
}
