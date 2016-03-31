<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160330221925 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reservationnotification (id INT AUTO_INCREMENT NOT NULL, reservation INT DEFAULT NULL, status INT DEFAULT NULL, subtype VARCHAR(255) NOT NULL, created DATETIME NOT NULL, sended DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, notificationType INT DEFAULT NULL, INDEX IDX_2D85196842C84955 (reservation), INDEX IDX_2D851968E069D4DA (notificationType), INDEX IDX_2D8519687B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservationnotification ADD CONSTRAINT FK_2D85196842C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE reservationnotification ADD CONSTRAINT FK_2D851968E069D4DA FOREIGN KEY (notificationType) REFERENCES nomenclator (nom_id)');
        $this->addSql('ALTER TABLE reservationnotification ADD CONSTRAINT FK_2D8519687B00651C FOREIGN KEY (status) REFERENCES nomenclator (nom_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reservationnotification');

    }
}
