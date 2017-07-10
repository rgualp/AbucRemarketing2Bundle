<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170710173147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pending_payment (pending_id INT AUTO_INCREMENT NOT NULL, user_casa INT NOT NULL, cancel_id INT DEFAULT NULL, user INT NOT NULL, type INT DEFAULT NULL, reason VARCHAR(500) NOT NULL, payment_date DATETIME NOT NULL, register_date DATETIME NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_A647739C5E411D4 (user_casa), INDEX IDX_A647739C736DEF02 (cancel_id), INDEX IDX_A647739C8D93D649 (user), INDEX IDX_A647739C8CDE5729 (type), PRIMARY KEY(pending_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C5E411D4 FOREIGN KEY (user_casa) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C736DEF02 FOREIGN KEY (cancel_id) REFERENCES cancel_payment (cancel_id)');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C8D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C8CDE5729 FOREIGN KEY (type) REFERENCES nomenclator (nom_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pending_payment');

    }
}
