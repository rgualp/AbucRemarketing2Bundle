<?php

namespace MyCp\FrontEndBundle\Helpers;

use Swift_Message;
use MyCp\mycpBundle\Entity\generalReservation;

/*
 * Service name: mycp.logger
 * */
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

    public function logSMS($message)
    {
        $path = $this->kernelRootDir . '/../app/logs/sms.log';
        file_put_contents($path, $message . PHP_EOL, FILE_APPEND);
    }


}
