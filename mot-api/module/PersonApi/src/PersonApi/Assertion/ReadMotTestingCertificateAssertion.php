<?php

namespace PersonApi\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Model\DvsaRole;
use PersonApi\Service\PersonalDetailsService;
use DvsaEntities\Entity\Person;

class ReadMotTestingCertificateAssertion implements AutoWireableInterface
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

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider,
        PersonalDetailsService $personalDetailsService
    ) {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->personalDetailsService = $personalDetailsService;
    }

    public function isGranted(Person $person, array $systemRoles)
    {
        try {
            $this->assertGranted($person, $systemRoles);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    public function assertGranted(Person $person, array $systemRoles)
    {
        if ($this->identityProvider->getIdentity()->getUserId() !== $person->getId()) {
            $this->personalDetailsService->assertViewGranted($person);
        }

        if ($this->hasDvsaRole($systemRoles)) {
            throw new UnauthorisedException(self::ERROR_DVSA_USER);
        }
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
