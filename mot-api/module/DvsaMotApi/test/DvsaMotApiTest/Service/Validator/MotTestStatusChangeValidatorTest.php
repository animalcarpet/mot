<?php
namespace DvsaMotApiTest\Service\Validator;

use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Site;
use DvsaMotApi\Service\MotTestStatusChangeService;
use DvsaMotApi\Service\MotTestStatusService;
use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class MotTestStatusChangeValidatorTest
 */
class MotTestStatusChangeValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestStatusChangeValidator $motTestStatusChangeValidator */
    private $motTestStatusChangeValidator;

    public function setUp()
    {
        $motTestStatusService = XMock::of(MotTestStatusService::class);
        $this->motTestStatusChangeValidator = new MotTestStatusChangeValidator($motTestStatusService);
    }

    public function testValidateMotTestNewStatusForAborted()
    {
        $motTest = MotTestObjectsFactory::activeMotTest();
        $newStatus = MotTestStatusName::ABORTED;

        $this->assertTrue(
            $this->motTestStatusChangeValidator->verifyThatStatusTransitionIsPossible($motTest, $newStatus)
        );
    }

    public function test_verifyThatStatusTransitionIsPossible_whenAbortingDemoTest_shouldThrowException()
    {
        $this->setExpectedException(BadRequestException::class);
        $demoTest = MotTestObjectsFactory::activeDemoTest();
        $newStatus = MotTestStatusName::ABORTED;

        $this->assertTrue(
            $this->motTestStatusChangeValidator->verifyThatStatusTransitionIsPossible($demoTest, $newStatus)
        );
    }

    public function test_verifyThatStatusTransitionIsPossible_whenAbandoningDemoTest_shouldThrowException()
    {
        $this->setExpectedException(BadRequestException::class);
        $demoTest = MotTestObjectsFactory::activeDemoTest();
        $newStatus = MotTestStatusName::ABANDONED;

        $this->assertTrue(
            $this->motTestStatusChangeValidator->verifyThatStatusTransitionIsPossible($demoTest, $newStatus)
        );
    }

    /**
     * @dataProvider dataProviderValidateDataForNewStatus
     */
    public function testValidateDataForNewStatus($data, $expectedException)
    {
        if (isset($expectedException)) {
            $this->setExpectedException($expectedException);
        }
        $this->motTestStatusChangeValidator->validateDataForNewStatus($data);
    }

    public function dataProviderValidateDataForNewStatus()
    {
        $newStatus = MotTestStatusName::ABORTED;
        $expectedException = \DvsaCommonApi\Service\Exception\BadRequestException::class;

        return [
            [
                'data'              => [
                    MotTestStatusChangeService::FIELD_STATUS            => $newStatus,
                    MotTestStatusChangeService::FIELD_REASON_FOR_CANCEL => 1
                ],
                'expectedException' => null,
            ],
            [
                'data'              => [
                    MotTestStatusChangeService::FIELD_STATUS => $newStatus,
                ],
                'expectedException' => $expectedException,
            ],
            [
                'data'              => [],
                'expectedException' => $expectedException,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderValidateDataForAbandonedMotTest
     */
    public function testValidateDataForAbandonedMotTest($data, $expectedException)
    {
        if (isset($expectedException)) {
            $this->setExpectedException($expectedException);
        }
        $this->motTestStatusChangeValidator->validateDataForAbandonedMotTest($data);
    }

    public function dataProviderValidateDataForAbandonedMotTest()
    {
        $expectedException = \DvsaCommonApi\Service\Exception\BadRequestException::class;

        return [
            [
                'data'              => [
                    MotTestStatusChangeService::FIELD_CANCEL_COMMENT => 'Comment Text',
                    MotTestStatusChangeService::FIELD_OTP            => '123456'
                ],
                'expectedException' => null,
            ],
            [
                'data'              => [
                    MotTestStatusChangeService::FIELD_CANCEL_COMMENT => 'Comment Text',
                ],
                'expectedException' => null,
            ],
            [
                'data'              => [
                    MotTestStatusChangeService::FIELD_OTP => '123456'
                ],
                'expectedException' => $expectedException,
            ],
            [
                'data'              => [],
                'expectedException' => $expectedException,
            ],
        ];
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage MOT Test status must be PASSED, FAILED, ACTIVE, ABORTED, ABORTED_VE or ABANDONED
     */
    public function testValidateMotTestNewStatusThrowsBadRequestExceptionForInvalidStatus()
    {
        $motTest = new MotTest();
        $motTest->setStatus($this->createMotTestActiveStatus());
        $newStatus = 'KINDA_PASSED';

        $this->motTestStatusChangeValidator->verifyThatStatusTransitionIsPossible($motTest, $newStatus);
    }

    public function testCheckSiteIdHasBeenEnteredTrueWithSiteId()
    {
        $motTest = new MotTest();
        $motTest->setVehicleTestingStation(new Site());

        $this->assertTrue($this->motTestStatusChangeValidator->checkSiteIdHasBeenEntered($motTest));
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Site ID - enter the site ID
     */
    public function testCheckSiteIdHasBeenEnteredThrowsWithNoSiteId()
    {
        $motTest = new MotTest();
        $this->motTestStatusChangeValidator->checkSiteIdHasBeenEntered($motTest);
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }
}
