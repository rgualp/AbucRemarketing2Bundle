<?php

namespace MyCp\mycpBundle\Command;

use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Helpers\Images;

class TestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp:testings')
                ->setDefinition(array())
                ->setDescription('Tests some functionalities')
                ->setHelp(<<<EOT
                Command <info>mycp_task:testings</info> do some tests.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $user = $em->getRepository("mycpBundle:user")->find(2086);

        $activationUrl = $this->getActivationUrl($user, "es");

        $activationUrl = str_replace(".com//", ".com/", $activationUrl);

        $output->writeln($activationUrl);

        $output->writeln("End of testings");
    }

    private function getActivationUrl($user, $userLocale)
    {
        $encodedString = $this->getContainer()->get("Secure")->getEncodedUserString($user);
        $enableUrl = $this->getContainer()->get("router")->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale
        ), true);
        return $enableUrl;
    }

}