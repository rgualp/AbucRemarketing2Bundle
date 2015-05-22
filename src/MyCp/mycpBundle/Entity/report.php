<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * report
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\reportRepository")
 */
class report
{
    /**
     * @var integer
     *
     * @ORM\Column(name="report_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $report_id;

    /**
     * @var string
     *
     * @ORM\Column(name="report_name", type="string", length=255)
     */
    private $report_name;

    /**
     * @var string
     *
     * @ORM\Column(name="report_route_name", type="string")
     */
    private $report_route_name;

    /**
     * @var string
     *
     * @ORM\Column(name="report_excel_export_route_name", type="string", nullable=true)
     */
    private $report_excel_export_route_name;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="report_category",referencedColumnName="nom_id")
     */
    private $report_category;

    /**
     * @ORM\OneToMany(targetEntity="reportParameter", mappedBy="parameter_report", cascade={"persist", "remove"})     *
     */
    private $parameters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    /**
     * Get report_id
     *
     * @return integer 
     */
    public function getReportId()
    {
        return $this->report_id;
    }

    /**
     * Set report_name
     *
     * @param string $reportName
     * @return report
     */
    public function setReportName($reportName)
    {
        $this->report_name = $reportName;
    
        return $this;
    }

    /**
     * Get report_name
     *
     * @return string 
     */
    public function getReportName()
    {
        return $this->report_name;
    }

    /**
     * Set report_category
     *
     * @param nomenclator $reportCategory
     * @return report
     */
    public function setReportCategory(nomenclator $reportCategory)
    {
        $this->report_category = $reportCategory;
    
        return $this;
    }

    /**
     * Get report_category
     *
     * @return nomenclator
     */
    public function getReportCategory()
    {
        return $this->report_category;
    }


    /**
     * Set report_route_name
     *
     * @param string $reportRouteName
     * @return report
     */
    public function setReportRouteName($reportRouteName)
    {
        $this->report_route_name = $reportRouteName;
    
        return $this;
    }

    /**
     * Get report_route_name
     *
     * @return string
     */
    public function getReportRouteName()
    {
        return $this->report_route_name;
    }

    /**
     * Set report_excel_export_route_name
     * @param $excelExportRouteName
     * @return $this
     */
    public function setReportExcelExportRouteName($excelExportRouteName)
    {
        $this->report_excel_export_route_name = $excelExportRouteName;

        return $this;
    }

    /**
     * Get report_excel_export_route_name
     *
     * @return string
     */
    public function getReportExcelExportRouteName()
    {
        return $this->report_excel_export_route_name;
    }

    /**
     * Add parameter
     *
     * @param reportParameter $parameter
     * @return report
     */
    public function addParameter(reportParameter $parameter)
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    /**
     * Remove parameter
     *
     * @param reportParameter $parameter
     */
    public function removePermission(reportParameter $parameter)
    {
        $this->parameters->removeElement($parameter);
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters
     * @param ArrayCollection $parameters
     * @return $this
     */
    public function setParameters(ArrayCollection $parameters)
    {
         $this->parameters = $parameters;
        return $this;
    }

    public function __toString(){
        return $this->report_name;
    }
}
