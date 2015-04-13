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

        $this->batchProcess->setBatchErrorMessages($savedMessage.$message);

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

    protected function processBatch()
    {
        if(!$this->hasErrors())
            $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_SUCCESS);
        else
            $this->batchProcess->setBatchStatus(batchStatus::BATCH_STATUS_WITH_ERRORS_ERROR);

    }

    protected function hasErrors()
    {
        return $this->batchProcess->getBatchErrorsCount() == 0 && $this->batchProcess->getBatchErrorMessages() == "";
    }

    protected function saveProcess()
    {
        $this->em->persist($this->batchProcess);
        $this->em->flush();
    }

    protected function getBatchProcessObject()
    {
        return $this->batchProcess;
    }

    protected abstract function configBatchProcess();
}

?>
