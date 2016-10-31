<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161025002805 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE newsletter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, creation_date DATETIME NOT NULL, sent TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_content (id INT AUTO_INCREMENT NOT NULL, newsletter INT DEFAULT NULL, language INT DEFAULT NULL, emailBody LONGTEXT NOT NULL, INDEX IDX_F17F93C67E8585C8 (newsletter), INDEX IDX_F17F93C6D4DB71B5 (language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_email (id INT AUTO_INCREMENT NOT NULL, newsletter INT DEFAULT NULL, language INT DEFAULT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_6DFF30807E8585C8 (newsletter), INDEX IDX_6DFF3080D4DB71B5 (language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE newsletter_content ADD CONSTRAINT FK_F17F93C67E8585C8 FOREIGN KEY (newsletter) REFERENCES newsletter (id)');
        $this->addSql('ALTER TABLE newsletter_content ADD CONSTRAINT FK_F17F93C6D4DB71B5 FOREIGN KEY (language) REFERENCES lang (lang_id)');
        $this->addSql('ALTER TABLE newsletter_email ADD CONSTRAINT FK_6DFF30807E8585C8 FOREIGN KEY (newsletter) REFERENCES newsletter (id)');
        $this->addSql('ALTER TABLE newsletter_email ADD CONSTRAINT FK_6DFF3080D4DB71B5 FOREIGN KEY (language) REFERENCES lang (lang_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsletter_content DROP FOREIGN KEY FK_F17F93C67E8585C8');
        $this->addSql('ALTER TABLE newsletter_email DROP FOREIGN KEY FK_6DFF30807E8585C8');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE newsletter_content');
        $this->addSql('DROP TABLE newsletter_email');

    }
}
