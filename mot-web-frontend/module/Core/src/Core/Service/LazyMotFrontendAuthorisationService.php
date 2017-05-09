<?php

namespace Core\Service;

use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;

/**
 * Implementation of MotAuthorizationServiceInterface that retrieves the user's roles from the
 * service tier and caches them in session.
 *
 * Also contains a few extra methods from MotFrontendAuthorizationServiceInterface that eventually should be removed.
 */
class LazyMotFrontendAuthorisationService extends AbstractMotAuthorisationService implements MotFrontendAuthorisationServiceInterface, MotAuthorizationRefresherInterface
{
    /** @var MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

    /** @var Client $restClient */
    private $restClient;

    public function __construct(MotIdentityProviderInterface $motIdentityProvider, Client $restClient)
    {
        $this->motIdentityProvider = $motIdentityProvider;
        $this->restClient = $restClient;
    }

    public function isVehicleExaminer()
    {
        return $this->hasRole(Role::VEHICLE_EXAMINER);
    }

    public function isTester()
    {
        return $this->hasRole(Role::TESTER_ACTIVE);
    }

    public function isDvsa()
    {
        $rolesAndPermissions = $this->getPersonAuthorization()->getRoles()->asArray();

        return DvsaRole::containDvsaRole($rolesAndPermissions['roles']);
    }

    /**
     * @return MotIdentityInterface
     */
    protected function getIdentity()
    {
        return $this->motIdentityProvider->getIdentity();
    }

    /**
     * @return PersonAuthorization
     */
    protected function getPersonAuthorization()
    {
        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity $identity */
        $identity = $this->motIdentityProvider->getIdentity();
        if (is_null($identity)) {
            return PersonAuthorization::emptyAuthorization();
        } else {
            /*
             * TODO There's no need to store this in the identity, just re-using it
             * for convenience (and that's how it used to work). Move to a separate
             * session store.
             */
            if (is_null($identity->getPersonAuthorization())) {
                $rolesArray = $this->restClient->get(
                    (new PersonUrlBuilder())->byId($identity->getUserId())->rbacRoles()
                )['data'];
                $personAuthorization = PersonAuthorization::fromArray($rolesArray);
                $identity->setPersonAuthorization($personAuthorization);
            }

            return $identity->getPersonAuthorization();
        }
    }

    public function refreshAuthorization()
    {
        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity $identity */
        $identity = $this->motIdentityProvider->getIdentity();
        $identity->setPersonAuthorization(null);
    }
}
