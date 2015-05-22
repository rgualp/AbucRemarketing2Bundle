<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * reportParameter
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\reportParameterRepository")
 */
class reportParameter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="parameter_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $parameter_id;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter_name", type="string", length=255)
     */
    private $parameter_name;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="parameter_type",referencedColumnName="nom_id")
     */
    private $parameter_type;

    /**
     * @ORM\ManyToOne(targetEntity="report",inversedBy="parameters")
     * @ORM\JoinColumn(name="parameter_report",referencedColumnName="report_id")
     */
    private $parameter_report;

    /**
     * Get parameter_id
     *
     * @return integer 
     */
    public function getParameterId()
    {
        return $this->parameter_id;
    }

    /**
     * Set parameter_name
     *
     * @param string $parameterName
     * @return reportParameter
     */
    public function setParameterName($parameterName)
    {
        $this->parameter_name = $parameterName;
    
        return $this;
    }

    /**
     * Get parameter_name
     *
     * @return string 
     */
    public function getParameterName()
    {
        return $this->parameter_name;
    }

    /**
     * Set parameter_type
     *
     * @param nomenclator $paramType
     * @return reportParameter
     */
    public function setParameterType(nomenclator $paramType)
    {
        $this->parameter_type = $paramType;
    
        return $this;
    }

    /**
     * Get parameter_type
     *
     * @return nomenclator
     */
    public function getParameterType()
    {
        return $this->parameter_type;
    }


    /**
     * Set parameter_report
     *
     * @param report $report
     * @return reportParameter
     */
    public function setParameterReport(report $report)
    {
        $this->parameter_report = $report;
    
        return $this;
    }

    /**
     * Get parameter_report
     *
     * @return report
     */
    public function getParameterReport()
    {
        return $this->parameter_report;
    }

    public function __toString(){
        return $this->parameter_name;
    }
}
