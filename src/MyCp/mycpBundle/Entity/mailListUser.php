<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * metaLang
 *
 * @ORM\Table(name="maillistuser")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\mailListUserRepository")
 */
class mailListUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="mail_list_user_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $mail_list_user_id;

    /**
     * @ORM\ManyToOne(targetEntity="mailList",inversedBy="")
     * @ORM\JoinColumn(name="mail_list",referencedColumnName="mail_list_id")
     */
    private $mail_list;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="mail_list_user",referencedColumnName="user_id")
     */
    private $mail_list_user;

    public function __construct()
    { }

    /**
     * Get mail_list_id
     *
     * @return integer
     */
    public function getMailListUserId()
    {
        return $this->mail_list_user_id;
    }

    /**
     * Set mail_list
     *
     * @param mailList $list
     * @return mailListUser
     */
    public function setMailList($list)
    {
        $this->mail_list = $list;

        return $this;
    }

    /**
     * Get mail_list
     *
     * @return mailList
     */
    public function getMailList()
    {
        return $this->mail_list;
    }

    /**
     * Set mail_list_user
     *
     * @param user $user
     * @return mailListUser
     */
    public function setMailListUser($user)
    {
        $this->mail_list_user = $user;

        return $this;
    }

    /**
     * Get mail_list_user
     *
     * @return user
     */
    public function getMailListUser()
    {
        return $this->mail_list_user;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Lista de correo ".$this->getMailList()->getMailListName()." - Usuario: ".$this->getMailListUser()->getUserCompleteName();
    }
}
