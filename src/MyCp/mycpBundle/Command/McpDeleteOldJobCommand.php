<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/*
 * This command must run weekly
 */

class McpDeleteOldJobCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:job')
                ->setDefinition(array())
                ->addOption('count-job', '', InputOption::VALUE_OPTIONAL, 'Ver total de job a ejecutarse como parametro se pasa true para los job listo a procesar y false para los que no')
                ->addOption('count-all-job', null, InputOption::VALUE_NONE, 'Ver total de job')
                ->addOption('count-old-job', '', InputOption::VALUE_OPTIONAL, 'Ver total de job menores que una fecha')
                ->addOption('delete-job', '', InputOption::VALUE_OPTIONAL, 'Eliminar todos los job pasandole una fecha de entrada')
                ->setDescription('Table job');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $count_job= $input->getOption('count-job');
        $count_all_job= $input->getOption('count-all-job');
        $delete_job= $input->getOption('delete-job');
        $count_old_job=$input->getOption('count-old-job');

        if(isset($count_job)){
            $job=$this->findJobs($count_job);
            if($count_job==1)
                $output->writeln('<info>Existen  '.count($job).' job listos para ejecutarse.</info>');
            else
                $output->writeln('<info>Existen  '.count($job).' job que no estan procesados.</info>');
            return;
        }
        if($count_all_job){
            $job=$this->findAllJobs();
            $output->writeln('<info>Existen  '.count($job).' job en la tabla de ramarketing.</info>');
            return;
        }
        if($delete_job){
            $job=$this->findJobsByDate($delete_job);
            $this->deleteJobsByDate($delete_job);
            $output->writeln('<info>Se eliminaron  '.count($job).' job.</info>');
            return;
        }
        if($count_old_job){
            $job=$this->findJobsByDate($count_old_job);
            $output->writeln('<info>Hay  '.count($job).' job.</info>');
            return;
        }

    }

    /**
     * @param $processed
     * @return mixed
     */
    private function findJobs($processed)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $repository = $em->getRepository('AbucRemarketingBundle:Job');
        $query = $repository->createQueryBuilder('r')
            ->where('r.processed = :processed')
            ->setParameter('processed', $processed)
            ->getQuery();
        return $query->getResult();
    }

    /**
     * @return mixed
     */
    private function findAllJobs()
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $repository = $em->getRepository('AbucRemarketingBundle:Job');
        $query = $repository->createQueryBuilder('r')
                            ->getQuery();
        return $query->getResult();
    }

    /**
     * @param $date date
     * @return int
     */
    private function findJobsByDate($date){
        $day = new \DateTime($date);
        $day = $day->format('Y-m-d H:i:s');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $repository = $em->getRepository('AbucRemarketingBundle:Job');
        $query = $repository->createQueryBuilder('r')
                    ->where('r.creationDate < :date')
                    ->setParameter('date', $day)
                    ->getQuery();
          return $query->getResult();

    }

    /**
     * @param $date
     * @return bool
     */
    private function deleteJobsByDate($date){
        $day = new \DateTime($date);
        $day = $day->format('Y-m-d H:i:s');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $repository = $em->getRepository('AbucRemarketingBundle:Job');
        $repository->createQueryBuilder('r')
            ->delete()
            ->where('r.creationDate < :date')
            ->setParameter('date', $day)
            ->getQuery()
            ->execute();
        return true;

    }


}
