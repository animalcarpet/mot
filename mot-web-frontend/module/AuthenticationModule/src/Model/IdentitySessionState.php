<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

/**
 * IdentitySessionState class.
 */
class IdentitySessionState
{
    /**
     * @var bool
     */
    private $isAuthenticated;

    /**
     * @var bool
     */
    private $shouldClearIdentity;

    /**
     * @param bool $isAuthenticated
     * @param bool $shouldClearSession
     */
    public function __construct($isAuthenticated, $shouldClearSession)
    {
        $this->isAuthenticated = $isAuthenticated;
        $this->shouldClearIdentity = $shouldClearSession;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * @return bool
     */
    public function shouldClearIdentity()
    {
        return $this->shouldClearIdentity;
    }
}
