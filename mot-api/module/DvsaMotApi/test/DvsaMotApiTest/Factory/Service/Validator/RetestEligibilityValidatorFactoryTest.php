<?php

namespace DvsaMotApiTest\Factory\Service\Validator;

use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Factory\Service\Validator\RetestEligibilityValidatorFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use NonWorkingDaysApi\NonWorkingDaysHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ClaimServiceFactoryTest.
 */
class RetestEligibilityValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(NonWorkingDaysHelper::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(MotTestRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(MysteryShopperHelper::class));

        $this->assertInstanceOf(
            RetestEligibilityValidator::class,
            (new RetestEligibilityValidatorFactory())->createService($mockServiceLocator)
        );
    }
}
