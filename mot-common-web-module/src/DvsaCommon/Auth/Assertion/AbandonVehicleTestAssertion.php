<?php

namespace DvsaCommon\Auth\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Exception\UnauthorisedException;

class AbandonVehicleTestAssertion
{
    const ERR_MSG_NOT_OWN_MOT_TEST = 'This test was started by another user and you are not allowed to abandon this test';

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityInterface
     */
    private $motIdentity;

    /**
     * @var string
     */
    private $code;

    /**
     * @var integer
     */
    private $testerId;

    /**
     * @var integer
     */
    private $vtsId;

    /**
     * @param MotIdentityInterface $motIdentity
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(
        MotIdentityInterface $motIdentity,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->motIdentity = $motIdentity;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setMotTestTypeCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param integer $id
     * @return $this
     *
     */
    public function setTesterId($id)
    {
        $this->testerId = $id;

        return $this;
    }

    /**
     * @param integer $id
     * @return $this
     */
    public function setVtsId($id)
    {
        $this->vtsId = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGranted()
    {
        try {
            $this->assertGranted();
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @throw DvsaCommon\Exception\UnauthorisedException
     */
    public function assertGranted()
    {
        if (
            $this->code === MotTestTypeCode::NORMAL_TEST
            || $this->code === MotTestTypeCode::RE_TEST
        ) {
            $this->assertCanTesterAbandonTest();
        } elseif ($this->code === MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST
        || $this->code === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        || $this->code === MotTestTypeCode::NON_MOT_TEST) {
            return ;
        } elseif (
            $this->code === MotTestTypeCode::TARGETED_REINSPECTION
            || $this->code === MotTestTypeCode::MOT_COMPLIANCE_SURVEY
            || $this->code === MotTestTypeCode::INVERTED_APPEAL
            || $this->code === MotTestTypeCode::STATUTORY_APPEAL
        ) {
            $this->assertCanVEAbandonTest();
        } else {
            $this->throwUnauthorisedException();
        }
    }

    private function assertCanTesterAbandonTest()
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::MOT_TEST_ABANDON_AT_SITE, $this->vtsId);
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_ABANDON);
        $this->assertUserOwnsTheMotTest();
    }

    private function assertCanVEAbandonTest()
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::MOT_TEST_ABANDON_AT_SITE, $this->vtsId);
        $this->assertUserOwnsTheMotTest();
    }

    private function throwUnauthorisedException()
    {
        throw new UnauthorisedException(self::ERR_MSG_NOT_OWN_MOT_TEST);
    }

    private function assertUserOwnsTheMotTest()
    {
        if ($this->testerId !== $this->motIdentity->getUserId()) {
            $this->throwUnauthorisedException();
        }
    }
}
