<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170320192654 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $data= <<<EOF
				INSERT INTO `hds_seo_header` (`id`, `decription`, `header_block_id`, `type_tag`, `tag`, `content`) VALUES
				(61, NULL, 9, 'link', '<link rel="alternate" href="https://www.mycasaparticular.com/it" hreflang="it">', NULL),
				(62, NULL, 9, 'link', '<link rel="alternate" href="https://www.mycasaparticular.com/fr" hreflang="fr">', NULL);
EOF;
        $this->addSql($data);

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
