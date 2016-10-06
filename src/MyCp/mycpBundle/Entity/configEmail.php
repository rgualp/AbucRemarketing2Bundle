<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * configEmail
 *
 * @ORM\Table(name="config_email")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\configEmailRepository")
 *
 */
class configEmail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

     /**
     * @var string
     *
     * @ORM\Column(name="subject_es", type="string", length=255)
     */
    private $subjectEs;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_en", type="string", length=255)
     */
    private $subjectEn;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_de", type="string", length=255)
     */
    private $subjectDe;

    /**
     * @var string
     *
     * @ORM\Column(name="introduction_es", type="string", length=500)
     */
    private $introductionEs;

    /**
     * @var string
     *
     * @ORM\Column(name="introduction_en", type="string", length=500)
     */
    private $introductionEn;

    /**
     * @var string
     *
     * @ORM\Column(name="introduction_de", type="string", length=500)
     */
    private $introductionDe;

    /**
     * @var string
     *
     * @ORM\Column(name="foward_es", type="string", length=500)
     */
    private $fowardEs;

    /**
     * @var string
     *
     * @ORM\Column(name="foward_en", type="string", length=500)
     */
    private $fowardEn;

    /**
     * @var string
     *
     * @ORM\Column(name="foward_de", type="string", length=500)
     */
    private $fowardDe;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subject_es
     *
     * @param string $subjectEs
     * @return configEmail
     */
    public function setSubjectEs($subjectEs)
    {
        $this->subjectEs = $subjectEs;
        return $this;
    }

    /**
     * Get subject_es
     *
     * @return string
     */
    public function getSubjectEs()
    {
        return $this->subjectEs;
    }

    /**
     * Set subject_en
     *
     * @param string $subjectEn
     * @return configEmail
     */
    public function setSubjectEn($subjectEn)
    {
        $this->subjectEn = $subjectEn;
        return $this;
    }

    /**
     * Get subject_en
     *
     * @return string
     */
    public function getSubjectEn()
    {
        return $this->subjectEn;
    }

    /**
     * Set subject_de
     *
     * @param string $subjectDe
     * @return configEmail
     */
    public function setSubjectDe($subjectDe)
    {
        $this->subjectDe = $subjectDe;
        return $this;
    }

    /**
     * Get subject_de
     *
     * @return string
     */
    public function getSubjectDe()
    {
        return $this->subjectDe;
    }

    /**
     * Set introduction_es
     *
     * @param string $introductionEs
     * @return configEmail
     */
    public function setIntroductionEs($introductionEs)
    {
        $this->introductionEs = $introductionEs;
        return $this;
    }

    /**
     * Get introduction_es
     *
     * @return string
     */
    public function getIntroductionEs()
    {
        return $this->introductionEs;
    }

    /**
     * Set introduction_en
     *
     * @param string $introductionEn
     * @return configEmail
     */
    public function setIntroductionEn($introductionEn)
    {
        $this->introductionEn = $introductionEn;
        return $this;
    }

    /**
     * Get introduction_en
     *
     * @return string
     */
    public function getIntroductionEn()
    {
        return $this->introductionEn;
    }

    /**
     * Set introduction_de
     *
     * @param string $introductionDe
     * @return configEmail
     */
    public function setIntroductionDe($introductionDe)
    {
        $this->introductionDe = $introductionDe;
        return $this;
    }

    /**
     * Get introduction_de
     *
     * @return string
     */
    public function getIntroductionDe()
    {
        return $this->introductionDe;
    }

    /**
     * Set foward_es
     *
     * @param string $fowardEs
     * @return configEmail
     */
    public function setFowardEs($fowardEs)
    {
        $this->fowardEs = $fowardEs;
        return $this;
    }

    /**
     * Get foward_es
     *
     * @return string
     */
    public function getFowardEs()
    {
        return $this->fowardEs;
    }

    /**
     * Set foward_en
     *
     * @param string $fowardEn
     * @return configEmail
     */
    public function setFowardEn($fowardEn)
    {
        $this->fowardEn = $fowardEn;
        return $this;
    }

    /**
     * Get foward_en
     *
     * @return string
     */
    public function getFowardEn()
    {
        return $this->fowardEn;
    }

    /**
     * Set foward_de
     *
     * @param string $fowardDe
     * @return configEmail
     */
    public function setFowardDe($fowardDe)
    {
        $this->fowardDe = $fowardDe;
        return $this;
    }

    /**
     * Get foward_de
     *
     * @return string
     */
    public function getFowardDe()
    {
        return $this->fowardDe;
    }
}