<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160824004939 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_client_reservation (client INT NOT NULL, reservation INT NOT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_1AE796DAC7440455 (client), INDEX IDX_1AE796DA42C84955 (reservation), INDEX IDX_1AE796DA7B982B81 (id_created_by), INDEX IDX_1AE796DA3DEB85F5 (id_modified_by), PRIMARY KEY(client, reservation)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DAC7440455 FOREIGN KEY (client) REFERENCES pa_client (id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA42C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_client_reservation');

    }
}
