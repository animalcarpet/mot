<?php

namespace UserAdmin\ViewModel\UserProfile;

use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

class TesterAuthorisationViewModel
{
    private $authorisationService;

    private $testerAuthorisation;

    private $testerId;

    public function __construct(
        $testerId,
        TesterAuthorisation $testerAuthorisation,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->testerId = $testerId;
        $this->testerAuthorisation = $testerAuthorisation;
        $this->authorisationService = $authorisationService;
    }

    public function getTesterAuthorisation()
    {
        return $this->testerAuthorisation;
    }

    public function getTesterId()
    {
        return $this->testerId;
    }

    public function shouldBeDisplayed()
    {
        return $this->testerAuthorisation->hasAnyTestingAuthorisation();
    }

    public function shouldDisplayGroupARecordDemoLink()
    {
        return $this->hasPermissionToAssessDemo()
            && $this->testerAuthorisation
            ->getGroupAStatus()->getCode() === AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
    }

    public function shouldDisplayGroupBRecordDemoLink()
    {
        return $this->hasPermissionToAssessDemo()
            && $this->testerAuthorisation->getGroupBStatus()
            ->getCode() == AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
    }

    private function hasPermissionToAssessDemo()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::ASSESS_DEMO_TEST);
    }

    public function canAlterTesterAuthorisation()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS);
    }
}
