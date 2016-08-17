<?php
namespace DvsaCommon\Auth\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisationForTestingMotStatus;
use DvsaCommon\Model\TesterAuthorisation;

class ViewTesterTestQualityAssertion implements AutoWireableInterface
{
    const ERROR_MESSAGE = "Can not read tester aggregated performance statistics";

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param MotIdentityProviderInterface $identityProvider
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider
    )
    {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
    }

    public function isGranted($personId, TesterAuthorisation $personAuthorisation)
    {
        try {
            $this->assertGranted($personId, $personAuthorisation);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    public function assertGranted($personId, TesterAuthorisation $personAuthorisation)
    {
        if ($this->hasCorrectQualificationStatus($personAuthorisation) === false) {
            throw new UnauthorisedException(self::ERROR_MESSAGE);
        }

        if ($this->isUpdatingOwnCertificate($personId) === false) {
            $this->authorisationService->assertGranted(PermissionInSystem::TESTER_VIEW_TEST_QUALITY);
        }
    }

    private function isUpdatingOwnCertificate($personId)
    {
        return $this->identityProvider->getIdentity()->getUserId() === $personId;
    }

    private function hasCorrectQualificationStatus(TesterAuthorisation $personAuthorisation)
    {
        $allowedStatuses = AuthorisationForTestingMotStatus::getPossibleStatusesForTqiAssertion();

        $hasCorrectGroupAStatus = false;
        $hasCorrectGroupBStatus = false;
        $groupBStatus = null;
        if ($personAuthorisation->hasGroupAStatus()) {
            $hasCorrectGroupAStatus = in_array($personAuthorisation->getGroupAStatus()->getCode(), $allowedStatuses);
        }

        if ($personAuthorisation->hasGroupBStatus()) {
            $hasCorrectGroupBStatus = in_array($personAuthorisation->getGroupBStatus()->getCode(), $allowedStatuses);
        }

        return ($hasCorrectGroupAStatus || $hasCorrectGroupBStatus);

    }
}
