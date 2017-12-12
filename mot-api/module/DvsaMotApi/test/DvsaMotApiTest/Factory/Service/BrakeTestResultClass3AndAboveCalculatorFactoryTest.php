<?php

namespace DvsaMotApiTest\Factory;

use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Factory\Service\BrakeTestResultClass3AndAboveCalculatorFactory;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;

class BrakeTestResultClass3AndAboveCalculatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            BrakeTestResultClass3AndAboveCalculatorFactory::class,
            BrakeTestResultClass3AndAboveCalculator::class,
            [
                'Feature\FeatureToggles' => FeatureToggles::class
            ]
        );
    }

}