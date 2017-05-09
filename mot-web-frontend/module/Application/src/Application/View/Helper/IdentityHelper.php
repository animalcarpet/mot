<?php

namespace Application\View\Helper;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\View\Helper\AbstractHelper;

/**
 * IdentityHelper - helper for view.
 *
 * accessible by this->identityHelper() in any *.phtml file
 */
class IdentityHelper extends AbstractHelper implements MotFrontendIdentityInterface
{
    /**
     * @var MotIdentityProviderInterface;
     */
    protected $identityProvider;

    public function __construct(MotFrontendIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
        $this->identity = $identityProvider->getIdentity();
    }

    /**
     * Returns the username e.g. user1@example.com.
     */
    public function getUsername()
    {
        return $this->identity->getUsername();
    }

    /**
     * Returns the user ID e.g. 5001.
     */
    public function getUserId()
    {
        return $this->identity->getUserId();
    }

    public function getDisplayName()
    {
        return $this->identity->getDisplayName();
    }

    /**
     * @return \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation
     */
    public function getCurrentVts()
    {
        return $this->identity->getCurrentVts();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->identity->getUuid();
    }

    /**
     * @return bool
     */
    public function isPasswordChangeRequired()
    {
        return $this->identity->isPasswordChangeRequired();
    }

    /**
     * @return bool
     */
    public function isAccountClaimRequired()
    {
        return $this->identity->isAccountClaimRequired();
    }

    /**
     * @return bool
     */
    public function hasPasswordExpired()
    {
        return $this->identity->hasPasswordExpired();
    }

    /**
     * @return bool
     */
    public function isAuthenticatedWithLostForgotten()
    {
        return $this->identity->isAuthenticatedWithLostForgotten();
    }
}
