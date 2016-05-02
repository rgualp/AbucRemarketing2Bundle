<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160430152947 extends AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE hds_seo_headerblock (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, decription LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE hds_seo_header (id INT AUTO_INCREMENT NOT NULL, header_block_id INT DEFAULT NULL, type_tag enum(\'meta\', \'link\'), tag LONGTEXT NOT NULL, content LONGTEXT DEFAULT NULL, decription LONGTEXT DEFAULT NULL, INDEX IDX_108BFAAD58308376 (header_block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE hds_seo_block (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, location VARCHAR(100) DEFAULT NULL, isActive TINYINT(1) NOT NULL, decription LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE hds_seo_blockcontent (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, header_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, decription LONGTEXT DEFAULT NULL, INDEX IDX_D6BCCE5CE9ED820C (block_id), INDEX IDX_D6BCCE5C2EF91FD8 (header_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE hds_seo_header ADD CONSTRAINT FK_108BFAAD58308376 FOREIGN KEY (header_block_id) REFERENCES hds_seo_headerblock (id)');
		$this->addSql('ALTER TABLE hds_seo_blockcontent ADD CONSTRAINT FK_D6BCCE5CE9ED820C FOREIGN KEY (block_id) REFERENCES hds_seo_block (id)');
		$this->addSql('ALTER TABLE hds_seo_blockcontent ADD CONSTRAINT FK_D6BCCE5C2EF91FD8 FOREIGN KEY (header_id) REFERENCES hds_seo_header (id)');


		$this->addSql("INSERT INTO `hds_seo_headerblock` (`id`, `name`) VALUES
						(1,'Pinterest'),
						(2, 'Apple'),
						(3, 'Android'),
						(4, 'Google'),
						(5, 'Window'),
						(6, 'Facebook'),
						(7, 'Twitter Summary Large Image Card'),
						(8, 'Twitter Card App'),
						(9, 'General');");

		$data= <<<EOF
				INSERT INTO `hds_seo_header` (`id`, `decription`, `header_block_id`, `type_tag`, `tag`, `content`) VALUES
				(1, NULL, 1, 'meta', '<meta name="p:domain_verify" content="ee3dd0cd95c625f8f2446d34712815ed"> ', NULL),
				(2, NULL, 1, 'meta', '<meta name="HandheldFriendly" content="True">', NULL),
				(3, NULL, 1, 'meta', '<meta name="MobileOptimized" content="320">', NULL),
				(4, NULL, 1, 'meta', '<meta name="viewport" content="initial-scale=1.0,width=device-width,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">', NULL),
				(5, NULL, 1, 'meta', '<meta http-equiv="cleartype" content="on">', NULL),
				(6, NULL, 1, 'meta', '<meta name="apple-mobile-web-app-title" content="MyCasaTrip">', NULL),
				(7, NULL, 1, 'meta', '<meta name="robots" content="NOYDIR,NOODP">', NULL),
				(8, NULL, 1, 'link', '<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">', NULL),
				(9, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="57x57" href="https://www.mycasaparticular.com/apple-touch-icon-57x57.png">', NULL),
				(10, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="60x60" href="https://www.mycasaparticular.com/apple-touch-icon-60x60.png">', NULL),
				(11, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="72x72" href="https://www.mycasaparticular.com/apple-touch-icon-72x72.png">', NULL),
				(12, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="76x76" href="https://www.mycasaparticular.com/apple-touch-icon-76x76.png">', NULL),
				(13, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="114x114" href="https://www.mycasaparticular.com/apple-touch-icon-114x114.png">', NULL),
				(14, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="120x120" href="https://www.mycasaparticular.com/apple-touch-icon-120x120.png">', NULL),
				(15, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="144x144" href="https://www.mycasaparticular.com/apple-touch-icon-144x144.png">', NULL),
				(16, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="152x152" href="https://www.mycasaparticular.com/apple-touch-icon-152x152.png">', NULL),
				(17, NULL, 2, 'link', '<link rel="apple-touch-icon" sizes="180x180" href="https://www.mycasaparticular.com/apple-touch-icon-180x180.png">', NULL),
				(18, NULL, 3, 'link', '<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32">', NULL),
				(19, NULL, 3, 'link', '<link rel="icon" type="image/png" href="android-chrome-192x192.png" sizes="192x192">', NULL),
				(20, NULL, 3, 'link', '<link rel="icon" type="image/png" href="favicon-96x96.png" sizes="96x96">', NULL),
				(21, NULL, 3, 'link', '<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16">', NULL),
				(22, NULL, 3, 'link', '<link rel="alternate" href="android-app://com.abuc.mycasatrip/http/mycasaparticular.com/en/">', NULL),
				(23, NULL, 4, 'meta', '<meta name="google-site-verification" content="4U1OC1LwZlFHAehLhCV4rt3YzWI_AyF7Gb0XqlaVEhE">', NULL),
				(24, NULL, 5, 'meta', '<meta name="msapplication-TileColor" content="#da532c">', NULL),
				(25, NULL, 5, 'meta', '<meta name="msapplication-TileImage" content="/mstile-144x144.png">', NULL),
				(26, NULL, 5, 'meta', '<meta name="theme-color" content="#ffffff">', NULL),
				(27, NULL, 6, 'meta', '<meta property="fb:app_id" content="187288694643718">', NULL),
				(28, NULL, 6, 'meta', '<meta property="fb:admins" content="1076790301,506404657,4700188">', NULL),
				(29, NULL, 6, 'meta', '<meta property="og:locale" content="en_US">', NULL),
				(30, NULL, 6, 'meta', '<meta property="og:type" content="website">', NULL),
				(31, NULL, 6, 'meta', '<meta property="og:url" content="https://www.mycasaparticular.com/en/">', NULL),
				(32, NULL, 6, 'meta', '<meta property="og:site_name" content="MyCasaParticular">', NULL),
				(33, NULL, 6, 'meta', '<meta property="og:image" content="https://www.mycasaparticular.com/frontend/imagen.jpg">', NULL),
				(34, NULL, 6, 'meta', '<meta property="og:site" content="www.mycasaparticular.com">', NULL),
				(35, NULL, 6, 'meta', '<meta property="og:title" content="Secure bookings in Cuba with more than 300 properties">', NULL),
				(36, NULL, 6, 'meta', '<meta property="og:description" content="MyCasaParticular is the leader in home bookings in Cuba.">', NULL),
				(37, NULL, 7, 'meta', '<meta name="twitter:card" content="summary_large_image">', NULL),
				(38, NULL, 7, 'meta', '<meta name="twitter:image:src" content="https://www.mycasaparticular.com/frontend/imagen.jpg">', NULL),
				(39, NULL, 7, 'meta', '<meta name="twitter:site" content="@MyCP">', NULL),
				(40, NULL, 7, 'meta', '<meta name="twitter:url" content="https://www.mycasaparticular.com/en/">', NULL),
				(41, NULL, 7, 'meta', '<meta name="twitter:description" content="MyCasaParticular is the leader in home bookings in Cuba.…">', NULL),
				(42, NULL, 8, 'meta', '<meta name="twitter:app:name:googleplay" content="MyCasaTrip">', NULL),
				(43, NULL, 8, 'meta', '<meta name="twitter:app:id:googleplay" content="com.abuc.mycasatrip">', NULL),
				(44, NULL, 8, 'meta', '<meta name="twitter:app:url:googleplay" content="https://www.mycasaparticular.com/en/mycasatrip/">', NULL),
				(45, NULL, 8, 'meta', '<meta name="twitter:title" content="Secure bookings in Cuba with more than 300 properties...">', NULL),
				(46, NULL, 8, 'meta', '<meta name="description" content="MyCasaParticular is the leader in home bookings in Cuba.">', NULL),
				(47, NULL, 9, 'link', '<link rel="canonical" href="https://www.mycasaparticular.com/en">', NULL),
				(48, NULL, 9, 'link', '<link rel="alternate" href="https://www.mycasaparticular.com/de" hreflang="de">', NULL),
				(49, NULL, 9, 'link', '<link rel="alternate" href="https://www.mycasaparticular.com/es" hreflang="es-ES">', NULL);
EOF;
		$this->addSql($data);

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
