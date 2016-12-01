<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161129173000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE failure (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, accommodation INT DEFAULT NULL, reservation INT DEFAULT NULL, type INT DEFAULT NULL, description LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_219B03C18D93D649 (user), INDEX IDX_219B03C12D385412 (accommodation), UNIQUE INDEX UNIQ_219B03C142C84955 (reservation), INDEX IDX_219B03C18CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE failure ADD CONSTRAINT FK_219B03C18D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE failure ADD CONSTRAINT FK_219B03C12D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE failure ADD CONSTRAINT FK_219B03C142C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE failure ADD CONSTRAINT FK_219B03C18CDE5729 FOREIGN KEY (type) REFERENCES nomenclator (nom_id)');
        $this->addSql('DROP TABLE tourist_failure');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tourist_failure (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, reservation INT DEFAULT NULL, user INT DEFAULT NULL, description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, creationDate DATETIME NOT NULL, UNIQUE INDEX UNIQ_45496E2142C84955 (reservation), INDEX IDX_45496E218D93D649 (user), INDEX IDX_45496E212D385412 (accommodation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E212D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E2142C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE tourist_failure ADD CONSTRAINT FK_45496E218D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('DROP TABLE failure');

    }
}
