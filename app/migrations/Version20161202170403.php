<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161202170403 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership_ranking_extra ADD endDate DATE NOT NULL, ADD rr INT DEFAULT NULL, ADD ri INT DEFAULT NULL, ADD sd INT DEFAULT NULL, ADD positiveComments INT DEFAULT NULL, ADD awards INT DEFAULT NULL, ADD confidence INT DEFAULT NULL, ADD newOffersReserved INT DEFAULT NULL, ADD failureCasa INT DEFAULT NULL, ADD negativeComments INT DEFAULT NULL, ADD snd INT DEFAULT NULL, ADD penalties INT DEFAULT NULL, ADD failureClients INT DEFAULT NULL, ADD facturation INT DEFAULT NULL, ADD ranking NUMERIC(10, 0) DEFAULT NULL, ADD place INT DEFAULT NULL, ADD destinationPlace INT DEFAULT NULL, ADD rankingPoints INT DEFAULT NULL, DROP uDetailsUpdated, DROP rRrI, DROP rsm, DROP acv,CHANGE updateddate startDate DATE NOT NULL');
        $this->addSql('ALTER TABLE ownership_ranking_extra ADD CONSTRAINT FK_5F0FA74FA7EA3032 FOREIGN KEY (rankingPoints) REFERENCES ranking_point (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F0FA74FA7EA3032 ON ownership_ranking_extra (rankingPoints)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership_ranking_extra DROP FOREIGN KEY FK_5F0FA74FA7EA3032');
        $this->addSql('DROP INDEX UNIQ_5F0FA74FA7EA3032 ON ownership_ranking_extra');
        $this->addSql('ALTER TABLE ownership_ranking_extra ADD updatedDate DATE NOT NULL, ADD uDetailsUpdated INT DEFAULT NULL, ADD rRrI INT DEFAULT NULL, ADD rsm INT DEFAULT NULL, ADD acv INT DEFAULT NULL, ADD extraRanking DOUBLE PRECISION DEFAULT NULL, ADD accommodationRanking DOUBLE PRECISION DEFAULT NULL, ADD comments INT DEFAULT NULL, DROP startDate, DROP endDate, DROP rr, DROP ri, DROP sd, DROP positiveComments, DROP awards, DROP confidence, DROP newOffersReserved, DROP failureCasa, DROP negativeComments, DROP snd, DROP penalties, DROP failureClients, DROP facturation, DROP ranking, DROP place, DROP destinationPlace, DROP rankingPoints');

    }
}
