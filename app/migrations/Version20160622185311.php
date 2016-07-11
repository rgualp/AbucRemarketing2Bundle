<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622185311 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cart ADD service_fee INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7B41917C2 FOREIGN KEY (service_fee) REFERENCES servicefee (id)');
        $this->addSql('CREATE INDEX IDX_BA388B7B41917C2 ON cart (service_fee)');
        $this->addSql('ALTER TABLE generalreservation ADD service_fee INT DEFAULT NULL');
        $this->addSql('ALTER TABLE generalreservation ADD CONSTRAINT FK_52BC9BBCB41917C2 FOREIGN KEY (service_fee) REFERENCES servicefee (id)');
        $this->addSql('CREATE INDEX IDX_52BC9BBCB41917C2 ON generalreservation (service_fee)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7B41917C2');
        $this->addSql('DROP INDEX IDX_BA388B7B41917C2 ON cart');
        $this->addSql('ALTER TABLE cart DROP service_fee');
        $this->addSql('ALTER TABLE generalreservation DROP FOREIGN KEY FK_52BC9BBCB41917C2');
        $this->addSql('DROP INDEX IDX_52BC9BBCB41917C2 ON generalreservation');
        $this->addSql('ALTER TABLE generalreservation DROP service_fee');
    }
}
