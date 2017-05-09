<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaCommonTest\TestUtils\OverridableExpectationBuilder;
use DvsaCommonTest\TestUtils\OverridableMockBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Site;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\OdometerReadingUpdatingService;
use DvsaMotApi\Service\Validator\MotTestValidator;

/**
 * Class OdometerReadingUpdatingServiceTest.
 *
 * Unit test exemplar
 */
class OdometerReadingUpdatingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthorisationServiceInterface
     */
    private $authorizationService;

    /** @var OverridableMockBuilder $motTestSecurityService */
    private $motTestSecurityServiceMockBuilder;

    /** @var OverridableMockBuilder $motTestValidatorMockBuilder */
    private $motTestValidatorMockBuilder;

    /** @var OverridableMockBuilder */
    private $performMotTestAssertionBuilder;

    public function setUp()
    {
        $this->authorizationService = XMock::of(AuthorisationServiceInterface::class);
        $this->motTestSecurityServiceMockBuilder = OverridableMockBuilder::of(MotTestSecurityService::class);
        $this->motTestValidatorMockBuilder = OverridableMockBuilder::of(MotTestValidator::class);
        $this->performMotTestAssertionBuilder = OverridableMockBuilder::of(ApiPerformMotTestAssertion::class);

        // given (default scenario)
        $this->allowedToModifyOdometerForTest(true);
        $this->currentTesterCanModifyTest(true);
    }

    public function testUpdateForMotTest_givenReadingValidationFails_shouldThrowProperException()
    {
        $this->setExpectedException(DataValidationException::class);

        // given
        $test = $this->createMotTest();
        $invalidReading = OdometerReadingDto::create()->setResultType(OdometerReadingResultType::OK);

        // when
        $this->createService()->updateForMotTest($invalidReading, $test);
    }

    public function testUpdateForMotTest_givenNoOdometer_shouldUpdateReading()
    {
        // given
        $test = $this->createMotTest();
        $newReading = OdometerReadingDto::create()->setResultType(OdometerReadingResultType::NO_ODOMETER);

        // when
        $this->createService()->updateForMotTest($newReading, $test);

        // then
        $this->assertEquals(
            OdometerReadingResultType::NO_ODOMETER,
            $test->getOdometerResultType(),
            'Incorrect result type has not been saved!'
        );
    }

    public function testUpdateForMotTest_givenOdometerUnreadable_shouldUpdateReading()
    {
        // given
        $test = $this->createMotTest();
        $reading = OdometerReadingDto::create()->setResultType(OdometerReadingResultType::NOT_READABLE);

        // when
        $this->createService()->updateForMotTest($reading, $test);

        // then
        $this->assertEquals(
            OdometerReadingResultType::NOT_READABLE,
            $test->getOdometerResultType(),
            'Incorrect result type has not been saved!'
        );
    }

    private static function createMotTest()
    {
        return (new MotTest())
            ->setId(1)
            ->setVehicleTestingStation(
                (new Site())->setId(1)
            );
    }

    private function createService()
    {
        return new OdometerReadingUpdatingService(
            $this->authorizationService,
            $this->motTestSecurityServiceMockBuilder->build(),
            $this->motTestValidatorMockBuilder->build(),
            $this->performMotTestAssertionBuilder->build()
        );
    }

    private function allowedToModifyOdometerForTest($decision)
    {
        $this->motTestSecurityServiceMockBuilder->setExpectation(
            OverridableExpectationBuilder::withMethodResult(
                'canModifyOdometerForTest',
                $this->returnValue($decision)
            )
        );
    }

    private function currentTesterCanModifyTest($decision)
    {
        $this->motTestSecurityServiceMockBuilder->setExpectation(
            OverridableExpectationBuilder::withMethodResult(
                'isCurrentTesterAssignedToVts',
                $this->returnValue($decision)
            )
        );
    }
}
