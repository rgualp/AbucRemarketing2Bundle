<?php

namespace MyCp\mycpBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationMailService extends Controller
{
	private $serviceNotificationUrl;
	private $notificationServiceApiKey;

	private $to;
	private $bcc;
	private $cc;
	private $subject;
	private $from_email;
	private $from_name;
	private $msg;
	private $email_type;

	public function __construct($serviceNotificationUrl, $notificationServiceApiKey)
	{
		$this->serviceNotificationUrl = $serviceNotificationUrl;
		$this->notificationServiceApiKey = $notificationServiceApiKey;

		$this->to =$this->bcc =$this->cc= array();
		$this->subject= $this->from_email= $this->from_name= $this->msg= $this->email_type= '';
	}

	public function setTo($addresses, $name = null)
	{
		if (!is_array($addresses) && isset($name)) {
			$addresses = array($addresses => $name);
		}

		$this->to= $addresses;
		return $this;
	}

	public function addTo($address, $name = null)
	{
		$this->to[$address] = $name;
		return $this;
	}

	public function setBcc($addresses, $name = null)
	{
		if (!is_array($addresses) && isset($name)) {
			$addresses = array($addresses => $name);
		}

		$this->bcc= $addresses;
		return $this;
	}

	public function addBcc($address, $name = null)
	{
		$this->bcc[$address] = $name;
		return $this;
	}

	public function setCc($addresses, $name = null)
	{
		if (!is_array($addresses) && isset($name)) {
			$addresses = array($addresses => $name);
		}

		$this->bcc= $addresses;
		return $this;
	}

	public function addCc($address, $name = null)
	{
		$this->cc[$address] = $name;
		return $this;
	}

	public function setSubject($subject)
	{
		$this->subject= $subject;

		return $this;
	}

	public function setFrom($mail, $name='')
	{
		$this->from_email= $mail;
		$this->from_name= $name;

		return $this;
	}

	public function setBody($body)
	{
		$this->msg= $body;

		return $this;
	}

	public function setEmailType($type){
		$this->email_type= $type;
	}

	public function sendEmail()
	{
		$data['mail'] = array(
			'project' => $this->notificationServiceApiKey,
			'to' => $this->to,
			'bcc' => $this->bcc,
			'cc' => $this->cc,
			'subject' => $this->subject,
			'from_email' => $this->from_email,
			'from_name' => $this->from_name,
			'msg' => $this->msg,
			'email_type' => $this->email_type
		);

		$url = $this->serviceNotificationUrl . '/api/email/add';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		$response = curl_exec($curl);//{"id":#}
		$info = curl_getinfo($curl);
		curl_close($curl);
		$code = $info['http_code'];//201==success

		#region Init all
		$this->to =$this->bcc =$this->cc= array();
		$this->subject= $this->from_email= $this->from_name= $this->msg= $this->email_type= '';
		#endregion

		return $code==201?true:false;
	}

}


