<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170504215544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("DROP PROCEDURE IF EXISTS sp_change_accommodation_codes");
        //Triggers en ownership
        $this->addSql("
            CREATE PROCEDURE sp_change_accommodation_codes (auxCode varchar(50), mainCode varchar(50))
            BEGIN
                set @auxAccommodation = (select MIN(o.own_id) from ownership o where o.own_mcp_code = auxCode);
                set @mainAccommodation = (select MIN(o.own_id) from ownership o where o.own_mcp_code = mainCode);

                if @auxAccommodation is not null and @mainAccommodation is not null then
                    UPDATE ownership
                    SET own_mcp_code = mainCode
                    WHERE own_id = @auxAccommodation;

                    UPDATE ownership
                    SET own_mcp_code = auxCode
                    WHERE own_id = @mainAccommodation;

                    set @auxUser = (SELECT MIN(u.user_casa_user) from usercasa u WHERE u.user_casa_ownership = @auxAccommodation);
                    set @mainUser = (SELECT MIN(u.user_casa_user) from usercasa u WHERE u.user_casa_ownership = @mainAccommodation);

                    if @auxUser is not null then
                    	UPDATE user
                    	SET user_name = mainCode
                    	WHERE user_id = @auxUser;
                    end if;

                    if @mainUser is not null then
                    	UPDATE user
                    	SET user_name = auxCode
                    	WHERE user_id = @mainUser;
                    end if;

                end if;
            END
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
