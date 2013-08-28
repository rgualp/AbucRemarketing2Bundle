<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faqLang
 *
 * @ORM\Table(name="faqlang")
 * @ORM\Entity
 */
class faqLang
{
    /**
     * @var integer
     *
     * @ORM\Column(name="faq_lang_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $faq_lang_id;

    /**
     * @var string
     *
     * @ORM\Column(name="faq_lang_question", type="text")
     */
    private $faq_lang_question;

    /**
     * @var string
     *
     * @ORM\Column(name="faq_lang_answer", type="text")
     */
    private $faq_lang_answer;

    /**
     * @ORM\ManyToOne(targetEntity="faq",inversedBy="")
     * @ORM\JoinColumn(name="faq_lang_faq_id",referencedColumnName="faq_id")
     */
    private $faq_lang_faq;

    /**
     * @ORM\ManyToOne(targetEntity="lang",inversedBy="")
     * @ORM\JoinColumn(name="faq_lang_lang_id",referencedColumnName="lang_id")
     */
    private $faq_lang_lang;


    /**
     * Get faq_lang_id
     *
     * @return integer 
     */
    public function getFaqLangId()
    {
        return $this->faq_lang_id;
    }

    /**
     * Set faq_lang_question
     *
     * @param string $faqLangQuestion
     * @return faqlang
     */
    public function setFaqLangQuestion($faqLangQuestion)
    {
        $this->faq_lang_question = $faqLangQuestion;
    
        return $this;
    }

    /**
     * Get faq_lang_question
     *
     * @return string 
     */
    public function getFaqLangQuestion()
    {
        return $this->faq_lang_question;
    }

    /**
     * Set faq_lang_answer
     *
     * @param string $faqLangAnswer
     * @return faqlang
     */
    public function setFaqLangAnswer($faqLangAnswer)
    {
        $this->faq_lang_answer = $faqLangAnswer;
    
        return $this;
    }

    /**
     * Get faq_lang_answer
     *
     * @return string 
     */
    public function getFaqLangAnswer()
    {
        return $this->faq_lang_answer;
    }

    /**
     * Set faq_lang_faq
     *
     * @param \MyCp\mycpBundle\Entity\faq $faqLangFaq
     * @return faqlang
     */
    public function setFaqLangFaq(\MyCp\mycpBundle\Entity\faq $faqLangFaq = null)
    {
        $this->faq_lang_faq = $faqLangFaq;
    
        return $this;
    }

    /**
     * Get faq_lang_faq
     *
     * @return \MyCp\mycpBundle\Entity\faq 
     */
    public function getFaqLangFaq()
    {
        return $this->faq_lang_faq;
    }

    /**
     * Set faq_lang_lang
     *
     * @param \MyCp\mycpBundle\Entity\lang $faqLangLang
     * @return faqlang
     */
    public function setFaqLangLang(\MyCp\mycpBundle\Entity\lang $faqLangLang = null)
    {
        $this->faq_lang_lang = $faqLangLang;
    
        return $this;
    }

    /**
     * Get faq_lang_lang
     *
     * @return \MyCp\mycpBundle\Entity\lang 
     */
    public function getFaqLangLang()
    {
        return $this->faq_lang_lang;
    }
}