<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170111164742 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_package ADD completePayment TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE pa_travel_agency ADD commission NUMERIC(2, 0) NOT NULL');

        $this->addSql('UPDATE pa_package SET completePayment = 0');
        $this->addSql('UPDATE pa_package SET completePayment = 1 WHERE name="Económico"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_package DROP completePayment');
        $this->addSql('ALTER TABLE pa_travel_agency DROP commission');
    }
}
