<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * userSession
 *
 * @ORM\Table(name="user_session")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userSessionRepository")
 */
class userSession {

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="user",inversedBy="")
	 * @ORM\JoinColumn(name="user_id",referencedColumnName="user_id")
	 */
	private $user;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="token", type="string", nullable=false)
	 * @Assert\NotBlank()
	 */
	private $token;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
	 */
	private $expiryDate;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 *
	 * @return userSession
	 */
	public function setToken( $token ) {
		$this->token = $token;

		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * Set expiryDate
	 *
	 * @param \DateTime $expiryDate
	 *
	 * @return userSession
	 */
	public function setExpiryDate( $expiryDate ) {
		$this->expiryDate = $expiryDate;

		return $this;
	}

	/**
	 * Get expiryDate
	 *
	 * @return \DateTime
	 */
	public function getExpiryDate() {
		return $this->expiryDate;
	}

	/**
	 * Set user
	 *
	 * @param \MyCp\mycpBundle\Entity\user $user
	 *
	 * @return userSession
	 */
	public function setUser( \MyCp\mycpBundle\Entity\user $user = null ) {
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return \MyCp\mycpBundle\Entity\user
	 */
	public function getUser() {
		return $this->user;
	}
}
