<?php

namespace PersonApi\Assertion;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Model\DvsaRole;
use DvsaEntities\Entity\Person;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\PersonalDetailsService;

class MotTestingAnnualCertificateAssertion implements AutoWireableInterface
{
    const ERROR_DVSA_USER = 'Can not create mot testing certificate for DVSA user';

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;
    /**
     * @var PersonalDetailsService
     */
    private $personalDetailsService;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider,
        PersonalDetailsService $personalDetailsService
    ) {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->personalDetailsService = $personalDetailsService;
    }

    public function assertGrantedCreate(Person $person)
    {
        $this->assertGranted($person, PermissionInSystem::CREATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER);
    }

    public function assertGrantedUpdate(Person $person)
    {
        $this->assertGranted($person, PermissionInSystem::UPDATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER);
    }

    public function assertGrantedView(Person $person)
    {
        $this->assertGranted($person, PermissionInSystem::VIEW_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER);
    }

    public function assertGrantedDelete($person)
    {
        $this->assertGranted($person, PermissionInSystem::REMOVE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER);
    }

    private function assertGranted(Person $person, $permission)
    {
        if ($this->identityProvider->getIdentity()->getUserId() !== $person->getId()) {
            $this->personalDetailsService->assertViewGranted($person);
            $this->authorisationService->assertGranted($permission);
        }

        if ($this->hasDvsaRole($this->getPersonSystemRoles($person->getId()))) {
            throw new UnauthorisedException(self::ERROR_DVSA_USER);
        }
    }

    private function getPersonSystemRoles($personId)
    {
        $personDetails = $this->personalDetailsService->get($personId);

        return $personDetails->getRoles()['system']['roles'];
    }

    private function hasDvsaRole(array $personSystemRoles)
    {
        foreach ($personSystemRoles as $role) {
            if (DvsaRole::isDvsaRole($role)) {
                return true;
            }
        }

        return false;
    }
}
