<?php

/**
 * Description of CalendarManager
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class CalendarManager {

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    private $container;

    /**
     * @var string
     */
    private $directoryPath;

    public function __construct(EntityManager $em, $container, $directoryPath) {
        $this->em = $em;
        $this->container = $container;
        $this->directoryPath = $directoryPath;
    }

    private function export($fileName) {
        $content = file_get_contents($this->directoryPath . $fileName);
        return new Response(
                $content, 200, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                )
        );
    }

    private function save($fileName) {

        if (!is_dir($this->directoryPath)) {
            mkdir($this->directoryPath, 0755, true);
        }

    }

}

?>
