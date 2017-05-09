<?php

namespace DvsaCommonApi\Authorisation\Assertion;

use DvsaCommon\Auth\Assertion\PerformMotTestAssertion;
use DvsaEntities\Entity\MotTest;

class ApiPerformMotTestAssertion
{
    private $assertion;

    public function __construct(
        PerformMotTestAssertion $assertion
    ) {
        $this->assertion = $assertion;
    }

    public function assertGranted(MotTest $motTest)
    {
        $testType = $motTest->getMotTestType()->getCode();
        $ownerId = $motTest->getTester()->getId();
        $vtsId = $motTest->getVehicleTestingStation() ? $motTest->getVehicleTestingStation()->getId() : null;

        $this->assertion->assertGranted($testType, $ownerId, $vtsId);
    }

    public function isGranted(MotTest $motTest)
    {
        $testType = $motTest->getMotTestType()->getCode();
        $ownerId = $motTest->getTester()->getId();
        $vtsId = $motTest->getVehicleTestingStation() ? $motTest->getVehicleTestingStation()->getId() : null;

        $this->assertion->isGranted($testType, $ownerId, $vtsId);
    }
}
