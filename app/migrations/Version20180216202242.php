<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180216202242 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("DROP TABLE IF EXISTS parents");
        $this->addSql("CREATE TABLE parents (parent_user_id INT NOT NULL, children_user_id INT NOT NULL, INDEX IDX_FD501D6AA76ED395 (parent_user_id), INDEX IDX_FD501D6A5F362C4E (children_user_id), PRIMARY KEY(parent_user_id, children_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE parents ADD CONSTRAINT FK_FD501D6AA76ED395 FOREIGN KEY (parent_user_id) REFERENCES user (user_id)");
        $this->addSql("ALTER TABLE parents ADD CONSTRAINT FK_FD501D6A5F362C4E FOREIGN KEY (children_user_id) REFERENCES user (user_id)");

// this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
