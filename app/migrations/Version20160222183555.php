<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160222183555 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE accommodation_award (award INT NOT NULL, accommodation INT NOT NULL, date DATE NOT NULL, INDEX IDX_86E40D1D8A5B2EE7 (award), INDEX IDX_86E40D1D2D385412 (accommodation), PRIMARY KEY(award, accommodation)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE award (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, ranking_value NUMERIC(10, 0) NOT NULL, icon_or_class_nam VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accommodation_award ADD CONSTRAINT FK_86E40D1D8A5B2EE7 FOREIGN KEY (award) REFERENCES award (id)');
        $this->addSql('ALTER TABLE accommodation_award ADD CONSTRAINT FK_86E40D1D2D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE accommodation_award DROP FOREIGN KEY FK_86E40D1D8A5B2EE7');
        $this->addSql('DROP TABLE accommodation_award');
        $this->addSql('DROP TABLE award');
    }
}
