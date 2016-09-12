<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160901222215 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE local_operation_assistant (id INT AUTO_INCREMENT NOT NULL, municipality INT DEFAULT NULL, fullname VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, phone2 VARCHAR(255) DEFAULT NULL, mobile VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_5CA8D61BC6F56628 (municipality), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE local_operation_assistant ADD CONSTRAINT FK_5CA8D61BC6F56628 FOREIGN KEY (municipality) REFERENCES municipality (mun_id)');

        $this->addSql("SET @munId = (SELECT MIN(mun_id) FROM municipality WHERE mun_name = 'Viñales' )");
        $this->addSql("INSERT INTO local_operation_assistant (municipality, fullname, phone, phone2, mobile) VALUES (@munId, 'Elieset Crespo Cuello', '(48) 695200', '(48) 695215', '52559958')");

        $this->addSql("SET @munId = (SELECT MIN(mun_id) FROM municipality WHERE mun_name = 'Trinidad' )");
        $this->addSql("INSERT INTO local_operation_assistant (municipality, fullname, phone, mobile) VALUES (@munId, 'Gonzalo López Castro', '(41) 992032', '58071643')");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE local_operation_assistant');

    }
}
