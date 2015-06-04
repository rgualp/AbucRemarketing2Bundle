<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150523031233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $columns = $this->connection->getSchemaManager()->listTableColumns('reportParameter');
        $existColumn = false;

        foreach ($columns as $column) {
            if($column->getName() == "reportParameter")
            {
                $existColumn = true;
                break;
            }
        }

        if(!$existColumn) {
            $this->addSql('ALTER TABLE reportParameter ADD parameter_order INT DEFAULT NULL');
            $this->addSql('update reportParameter set parameter_order = 1');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reportParameter DROP parameter_order');
    }
}
