<?php

namespace Core\Authorisation\Assertion;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Auth\Assertion\PerformMotTestAssertion;

class WebPerformMotTestAssertion
{
    private $assertion;

    public function __construct(PerformMotTestAssertion $assertion)
    {
        $this->assertion = $assertion;
    }

    public function assertGranted(MotTest $motTestData)
    {
        $testType = $this->extractTestType($motTestData);
        $ownerId = $this->extractOwnerId($motTestData);
        $vtsId = $this->extractVtsIdIfExists($motTestData);

        $this->assertion->assertGranted($testType, $ownerId, $vtsId);
    }

    public function isGranted($motTestData)
    {
        $testType = $this->extractTestType($motTestData);
        $ownerId = $this->extractOwnerId($motTestData);
        $vtsId = $this->extractVtsIdIfExists($motTestData);

        $this->assertion->isGranted($testType, $ownerId, $vtsId);
    }

    /**
     * @param MotTest $motTestData
     *
     * @return string
     */
    private function extractTestType(MotTest $motTestData)
    {
        $testType = $motTestData->getTestTypeCode();

        return $testType;
    }

    /**
     * @param MotTest $motTestData
     *
     * @return int
     */
    private function extractOwnerId(MotTest $motTestData)
    {
        $tester = $motTestData->getTester();

        return $tester->getId();
    }

    /**
     * @param MotTest $motTestData
     *
     * @return int
     */
    private function extractVtsIdIfExists(MotTest $motTestData)
    {
        $vtsId = $motTestData->getSiteId();

        return $vtsId;
    }
}
