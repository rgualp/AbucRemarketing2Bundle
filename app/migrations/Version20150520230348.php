<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520230348 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generalreservation ADD gen_res_nights INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generalreservation DROP gen_res_nights');
    }

    /**
     * Calculate number of nights and re-calculating saved price if price night is set in corresponding ownershipReservation
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        /*$em = $this->container->get('doctrine.orm.entity_manager');

        $startIndex = 0;
        $pageSize = 10;
        $totalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsForNightCounterTotal();

        while($startIndex < $totalReservations) {
            $generalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsByPagesForNightsCounter($startIndex, $pageSize);

        foreach($generalReservations as $gres)
        {
            $reservations = $em->getRepository("mycpBundle:ownershipReservation")->findBy(array("own_res_gen_res_id" => $gres->getGenResId()));
            $nights = 0;
            $price = 0;

            foreach($reservations as $reservation)
            {
                $nights += $reservation->getOwnResNights();

                if($reservation->getOwnResNightPrice() != 0)
                    $price += $reservation->getOwnResNightPrice() * $reservation->getOwnResNights();
                else
                    $price += $reservation->getOwnResTotalInSite();
            }

            $gres->setGenResNights($nights);
            if($price != $gres->getGenResTotalInSite())
                $gres->setGenResTotalInSite($price);

            $em->persist($gres);
        }

            $em->flush();
            $startIndex += $pageSize;
        }*/
    }
}
