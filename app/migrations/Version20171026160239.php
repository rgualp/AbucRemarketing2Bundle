<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171026160239 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE hds_seo_header CHANGE type_tag type_tag enum('meta', 'link','title')");
        $this->addSql("INSERT INTO `hds_seo_header` (`id`, `decription`, `header_block_id`, `type_tag`, `tag`, `content`) VALUES
						(63, NULL, 9, 'title', '<title>Example</title>', NULL);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE hds_seo_header DROP FOREIGN KEY FK_108BFAAD58308376');
        $this->addSql('ALTER TABLE hds_seo_blockcontent DROP FOREIGN KEY FK_D6BCCE5C2EF91FD8');
        $this->addSql('ALTER TABLE hds_seo_blockcontent DROP FOREIGN KEY FK_D6BCCE5CE9ED820C');
        $this->addSql('DROP TABLE hds_seo_headerblock');
        $this->addSql('DROP TABLE hds_seo_header');
        $this->addSql('DROP TABLE hds_seo_block');
        $this->addSql('DROP TABLE hds_seo_blockcontent');
    }
}
