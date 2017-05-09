<?php

namespace DvsaAuthorisation\Service;

use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Constants\Role;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Model\PersonAuthorization;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\RbacRepository;
use Zend\Authentication\AuthenticationService;

/**
 * Implementation of AuthorisationServiceInterface that queries the database for roles and permissions.
 */
class AuthorisationService extends AbstractMotAuthorisationService implements AuthorisationServiceInterface
{
    const HERO_USER = 'user';
    const HERO_VE = 'vehicle-examiner';
    const HERO_AEDM = 'aedm';
    const HERO_TESTER_APPLICANT = 'testerApplicant';
    const HERO_TESTER = 'tester';
    const HERO_DVSA_ADMIN = 'admin';
    const HERO_FINANCE = 'finance';

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RbacRepository
     */
    private $rbacRepository;

    /**
     * An array containing PersonAuthorization objects for user ID's.
     *
     * @var array
     */
    private $cachedPersonAuthorization = [];

    /**
     * @param AuthenticationService $authenticationService
     * @param RbacRepository        $rbacRepository
     */
    public function __construct(
        AuthenticationService $authenticationService,
        RbacRepository $rbacRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->rbacRepository = $rbacRepository;
    }

    /**
     * Set the cache back to an empty array.
     */
    public function flushAuthorisationCache()
    {
        $this->cachedPersonAuthorization = [];
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function isAuthenticatedAsPerson($id)
    {
        return $id == $this->authenticationService->getIdentity()->getUserId();
    }

    /**
     * @param $id
     *
     * @throws UnauthorisedException
     */
    public function assertAuthenticatedAsPerson($id)
    {
        if (!$this->isAuthenticatedAsPerson($id)) {
            throw new UnauthorisedException('You are not authorised to access this resource');
        }
    }

    /**
     * Get the PersonAuthorization object for a user by ID. If $personId is
     * not supplied then the ID of the logged in user will be used.
     *
     * @param null|int $personId The ID of the user to retrieve the authorisation for
     *
     * @return PersonAuthorization
     */
    public function getPersonAuthorization($personId = null)
    {
        if (is_null($personId)) {
            // If no $personId is supplied, get the authenticated user ID
            $personId = $this->authenticationService->getIdentity()->getUserId();
        }

        if (!isset($this->cachedPersonAuthorization[$personId])) {
            // Get the authorisation details for the $personId
            $this->cachedPersonAuthorization[$personId] = $this->rbacRepository->authorizationDetails($personId);
        }

        return $this->cachedPersonAuthorization[$personId];
    }

    public function getAuthorizationDataAsArray()
    {
        return $this->getPersonAuthorization()->asArray();
    }

    public function personHasRole($person, $roleName)
    {
        if (!$person instanceof Person) {
            throw new \Exception('Expecting a Person, got '.get_class($person));
        }

        return $this->personIdHasRole(
            $person->getId(),
            $roleName
        );
    }

    public function getIdentity()
    {
        return $this->authenticationService->getIdentity();
    }

    private function personIdHasRole($personId, $roleName)
    {
        return $this->rbacRepository->personIdHasRole($personId, $roleName);
    }

    /**
     * @param null|int $personId The ID of the user to retrieve the hero status for
     *
     * @return mixed
     */
    public function getHero($personId = null)
    {
        if (is_null($personId)) {
            // If no $personId is supplied, get the authenticated user ID
            $personId = $this->authenticationService->getIdentity()->getUserId();
        }

        $personAuthorisation = $this->getPersonAuthorization($personId);

        if ($personAuthorisation->isAdmin()) {
            return self::HERO_DVSA_ADMIN;
        }

        if ($this->personIdHasRole($personId, Role::TESTER_ACTIVE) && $personAuthorisation->hasRoleAtAnySite(Role::TESTER)) {
            return self::HERO_TESTER;
        } else {
            if ($this->personIdHasRole($personId, Role::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED)
                || $this->personIdHasRole($personId, Role::TESTER_APPLICANT_DEMO_TEST_REQUIRED)
            ) {
                return self::HERO_TESTER_APPLICANT;
            }
        }

        if ($personAuthorisation->isVe()) {
            return self::HERO_VE;
        }

        if ($personAuthorisation->isFinance()) {
            return self::HERO_FINANCE;
        }

        if ($personAuthorisation->isAedm()) {
            return self::HERO_AEDM;
        }

        return self::HERO_USER;
    }
}
