<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180305143956 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE pa_tour_operator SET pa_tour_operator.travel_agency = 37
where pa_tour_operator.tourOperator=32531 or 
pa_tour_operator.tourOperator=40601 or
pa_tour_operator.tourOperator=40898 or
pa_tour_operator.tourOperator=41064 or
pa_tour_operator.tourOperator=41065 or
pa_tour_operator.tourOperator=41066 or
pa_tour_operator.tourOperator=41067 or
pa_tour_operator.tourOperator=41068 or
pa_tour_operator.tourOperator=41069 or 
pa_tour_operator.tourOperator=41348 or 
pa_tour_operator.tourOperator=42390 or 
pa_tour_operator.tourOperator=42392 or 
pa_tour_operator.tourOperator=42393 or 
pa_tour_operator.tourOperator=42695
;");
        $this->addSql("UPDATE user SET user.user_role = 'ROLE_CLIENT_PARTNER_TOUROPERATOR',user.user_subrole=18
where user.user_id=32531 or 
user.user_id=40601 or
user.user_id=40898 or
user.user_id=41064 or
user.user_id=41065 or
user.user_id=41066 or
user.user_id=41067 or
user.user_id=41068 or
user.user_id=41069 or 
user.user_id=41348 or 
user.user_id=42390 or 
user.user_id=42392 or 
user.user_id=42393
;");
        $this->addSql("UPDATE user SET user.user_role = 'ROLE_ECONOMY_PARTNER',user.user_subrole=17
where user.user_id=42695 
;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
