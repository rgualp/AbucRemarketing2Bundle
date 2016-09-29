<?php

namespace MyCp\FrontEndBundle\Helpers;

use Swift_Message;
use MyCp\mycpBundle\Entity\generalReservation;

class Logger {

	private $kernelRootDir;

	public function __construct($kernelRootDir) {
		$this->kernelRootDir = $kernelRootDir;
	}

    public function logMail($message)
    {
        $path = $this->kernelRootDir . '/../app/logs/mails.log';
        file_put_contents($path, $message . PHP_EOL, FILE_APPEND);
    }


}
