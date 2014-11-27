<?php

namespace MyCp\mycpBundle\JobData;

use Abuc\RemarketingBundle\JobData\JobData;

class UserJobData extends JobData
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @param int|null $userId, string|null $sessionId
     */
    public function __construct($userId = null, $sessionId = null)
    {
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }

    /**
     * Set user ID.
     *
     * @param $userId
     * @return $this For a fluent interface.
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Returns user ID.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set session ID.
     *
     * @param $sessionId
     * @return $this For a fluent interface.
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * Returns session ID.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
