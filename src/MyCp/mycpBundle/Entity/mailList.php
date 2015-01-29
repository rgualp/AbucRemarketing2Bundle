<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * metaLang
 *
 * @ORM\Table(name="maillist")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\mailListRepository")
 */
class mailList
{
    /**
     * All allowed mail functions
     */
    const LIST_NEW_ACCOMMODATION_COMMAND = 1;


    /**
     * Contains all possible mail functions
     *
     * @var array
     */
    private $mailFunctions = array(
        self::LIST_NEW_ACCOMMODATION_COMMAND,

    );

    /**
     * @var integer
     *
     * @ORM\Column(name="mail_list_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $mail_list_id;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_list_name", type="string", length=255)
     */
    private $mail_list_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="mail_list_function", type="integer")
     */
    private $mail_list_function;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mail_list_creation_date", type="date")
     */
    private $mail_list_creation_date;

    public function __construct()
    {
        $this->mail_list_creation_date = new \DateTime();
    }

    /**
     * Get mail_list_id
     *
     * @return integer
     */
    public function getMailListId()
    {
        return $this->mail_list_id;
    }

    /**
     * Set mail_list_name
     *
     * @param string $name
     * @return mailList
     */
    public function setMailListName($name)
    {
        $this->mail_list_name = $name;

        return $this;
    }

    /**
     * Get mail_list_name
     *
     * @return string
     */
    public function getMailListName()
    {
        return $this->mail_list_name;
    }

    /**
     * Set mail_list_function
     *
     * @param integer $function
     * @return mailList
     */
    public function setMailListFunction($function)
    {
        if (!in_array($function, $this->mailFunctions)) {
            throw new \InvalidArgumentException("Mail function $function not allowed");
        }

        $this->mail_list_function = $function;

        return $this;
    }

    /**
     * Get mail_list_function
     *
     * @return integer
     */
    public function getMailListFunction()
    {
        return $this->mail_list_function;
    }

    /**
     * Set mail_list_creation_date
     *
     * @param DateTime $date
     * @return mailList
     */
    public function setMailListCreationDate($date)
    {
        $this->mail_list_creation_date = $date;

        return $this;
    }

    /**
     * Get mail_list_creation_date
     *
     * @return DateTime
     */
    public function getMailListCreationDate()
    {
        return $this->mail_list_creation_date;
    }

    public static function getMailFunctionName($functionId)
    {
        switch($functionId)
        {
            case mailList::LIST_NEW_ACCOMMODATION_COMMAND: return "Comando Nuevos Alojamientos";
        }
    }

    public static function getAllMailFunctions()
    {
        $results = array();

        $index = mailList::LIST_NEW_ACCOMMODATION_COMMAND;
        $results[$index] = mailList::getMailFunctionName($index);

        return $results;
    }
}
