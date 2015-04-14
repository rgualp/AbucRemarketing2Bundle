<?php

/**
 * Description of ExcelReader
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use MyCp\mycpBundle\Entity\batchStatus;
use Doctrine\ORM\EntityManager;
use MyCp\mycpBundle\Entity\batchProcess;

abstract class BatchProcessManager {

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $batchProcess;

    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->batchProcess = new batchProcess();
        $this->configBatchProcess();
    }

    protected function startProcess()
    {
        $this->batchProcess->setBatchStartDate(new \DateTime());
        $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_INCOMPLETE);
        $this->batchProcess->setBatchElementsCount(0);
        $this->batchProcess->setBatchErrorsCount(0);
        $this->batchProcess->setBatchSavedElementsCount(0);
    }

    protected function endProcess()
    {
        $this->batchProcess->setBatchEndDate(new \DateTime());
    }

    protected function addError($errorMessage)
    {
        $savedErrorMessage = $this->batchProcess->getBatchErrorMessages();
        $errorCount = $this->batchProcess->getBatchErrorsCount();
        $errorCount += 1;

        if($savedErrorMessage != "")
        {
            $savedErrorMessage .= "<br/>";
        }

        $this->batchProcess->setBatchErrorsCount($errorCount);
        $this->batchProcess->setBatchErrorMessages($savedErrorMessage.$errorMessage);

    }

    protected function addMessage($message)
    {
        $savedMessage = $this->batchProcess->getBatchMessages();

        if($savedMessage != "")
        {
            $savedMessage .= "<br/>";
        }

        $this->batchProcess->setBatchMessages($savedMessage.$message);

    }

    protected function addElement()
    {
        $batchElements = $this->batchProcess->getBatchElementsCount();
        $batchElements += 1;
        $this->batchProcess->setBatchElementsCount($batchElements);
    }

    protected function addSavedElement()
    {
        $batchElements = $this->batchProcess->getBatchSavedElementsCount();
        $batchElements += 1;
        $this->batchProcess->setBatchSavedElementsCount($batchElements);
    }

    protected function setStatus()
    {
        if(!$this->hasErrors())
        {
            if($this->batchProcess->getBatchElementsCount() == $this->batchProcess->getBatchSavedElementsCount())
                $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_SUCCESS);
            else
                $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_INCOMPLETE);
        }

        else
            $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_WITH_ERRORS_ERROR);

    }

    protected function hasErrors()
    {
        return $this->batchProcess->getBatchErrorsCount() > 0 || $this->batchProcess->getBatchErrorMessages() != "";
    }

    protected function saveProcess()
    {
        $this->reopenEntityManager();
        $this->em->persist($this->batchProcess);
        $this->em->flush();
    }

    protected function getBatchProcessObject()
    {
        return $this->batchProcess;
    }

    protected function reopenEntityManager()
    {
        if (!$this->em->isOpen()) {
            $this->em = $this->em->create(
                $this->em->getConnection(),
                $this->em->getConfiguration()
            );
        }
    }

    protected abstract function configBatchProcess();
}

?>
