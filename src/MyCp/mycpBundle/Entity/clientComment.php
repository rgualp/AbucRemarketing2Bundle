<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * clientComment
 *
 * @ORM\Table(name="clientComment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\clientCommentRepository")
 *
 */
class clientComment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="comment_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $comment_id;

    /**
     * @var string
     *
     * @ORM\Column(name="comment_text", type="text")
     */
    private $comment_text;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="comment_client_user",referencedColumnName="user_id")
     */
    private $comment_client_user;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="comment_staff_user",referencedColumnName="user_id")
     */
    private $comment_staff_user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="comment_date", type="datetime")
     */
    private $comment_date;

    public function __construct()
    {
        $this->comment_date = new \DateTime();
    }

    /**
     * Get comment_id
     *
     * @return integer
     */
    public function getCommentId()
    {
        return $this->comment_id;
    }

    /**
     * Get comment_text
     *
     * @return string
     */
    public function getCommentText()
    {
        return $this->comment_text;
    }

    /**
     * Set comment_text
     *
     * @param string $commentText
     * @return clientComment
     */
    public function setCommentText($commentText)
    {
        $this->comment_text = $commentText;

        return $this;
    }

    /**
     * Get comment_client_user
     *
     * @return user
     */
    public function getCommentClientUser()
    {
        return $this->comment_client_user;
    }

    /**
     * Set comment_client_user
     *
     * @param user $commentClient
     * @return clientComment
     */
    public function setCommentClientUser(user $commentClient)
    {
        $this->comment_client_user = $commentClient;

        return $this;
    }

    /**
     * Get comment_staff_user
     *
     * @return user
     */
    public function getCommentStaffUser()
    {
        return $this->comment_staff_user;
    }

    /**
     * Set comment_staff_user
     *
     * @param user $commentStaffUser
     * @return clientComment
     */
    public function setCommentStaffUser(user $commentStaffUser)
    {
        $this->comment_staff_user = $commentStaffUser;

        return $this;
    }

    /**
     * Set comment_date
     *
     * @param \DateTime $date
     * @return clientComment
     */
    public function setCommentDate($date)
    {
        $this->comment_date = $date;

        return $this;
    }

    /**
     * Get comment_date
     *
     * @return \DateTime
     */
    public function getCommentDate()
    {
        return $this->comment_date;
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Comentario sobre el cliente ".$this->getCommentClientUser()->getUserCompleteName();
    }
}