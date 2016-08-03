<?php
namespace DvsaMotApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\OverridableExpectationBuilder;
use DvsaCommonTest\TestUtils\OverridableMockBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Dto\Common\OdometerReadingDTO;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\OdometerReadingRepository;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\OdometerReadingUpdatingService;
use DvsaMotApi\Service\Validator\MotTestValidator;

/**
 * Unit test exemplar
 *
 * Class OdometerReadingUpdatingServiceTest
 *
 * @package DvsaMotApiTest\Service
 */
class OdometerReadingUpdatingServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var OverridableMockBuilder $motTestSecurityService */
    private $odometerReadingRepositoryMockBuilder;

    private $authorizationService;

    /** @var OverridableMockBuilder $motTestSecurityService */
    private $motTestSecurityServiceMockBuilder;

    /** @var  OverridableMockBuilder $motTestValidatorMockBuilder */
    private $motTestValidatorMockBuilder;

    /** @var OverridableMockBuilder */
    private $performMotTestAssertionBuilder;

    public function setUp()
    {
        $this->authorizationService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);
        $this->odometerReadingRepositoryMockBuilder = OverridableMockBuilder::of(OdometerReadingRepository::class);
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
        $invalidReading = OdometerReadingDTO::create()->setResultType(OdometerReadingResultType::OK);

        // when
        $this->createService()->updateForMotTest($invalidReading, $test);
    }

    public function testUpdateForMotTest_givenNoOdometer_shouldUpdateReading()
    {
        // given
        $test = $this->createMotTest();
        $newReading = OdometerReadingDTO::create()->setResultType(OdometerReadingResultType::NO_ODOMETER);

        // when
        $this->createService()->updateForMotTest($newReading, $test);

        // then
        $this->assertNotEmpty($test->getOdometerReading(), "Reading has not been saved!");
    }

    public function testUpdateForMotTest_givenOdometerUnreadable_shouldUpdateReading()
    {
        // given
        $test = $this->createMotTest();
        $reading = OdometerReadingDTO::create()->setResultType(OdometerReadingResultType::NOT_READABLE);

        // when
        $this->createService()->updateForMotTest($reading, $test);

        // then
        $this->assertNotEmpty($test->getOdometerReading(), "Reading has not been saved!");
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
            $this->odometerReadingRepositoryMockBuilder->build(),
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
                "canModifyOdometerForTest",
                $this->returnValue($decision)
            )
        );
    }

    private function currentTesterCanModifyTest($decision)
    {
        $this->motTestSecurityServiceMockBuilder->setExpectation(
            OverridableExpectationBuilder::withMethodResult(
                "isCurrentTesterAssignedToVts",
                $this->returnValue($decision)
            )
        );
    }
}
