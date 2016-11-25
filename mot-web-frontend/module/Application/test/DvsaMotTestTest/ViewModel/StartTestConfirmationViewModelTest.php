<?php

namespace DvsaMotTestTest\ViewModel\MotTestLog;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaMotTest\ViewModel\StartTestConfirmationViewModel;

class StartTestConfirmationViewModelTest extends \PHPUnit_Framework_TestCase
{

    const DUMMY_OBFUSCATED_VEHICLE_ID = '1234';
    const DUMMY_NO_REGISTRATION = '1';
    const DUMMY_VEHICLE_SOURCE = 'vehicleId';
    const DUMMY_IN_PROGRESS_TEST_EXISTS = true;
    const DUMMY_SEARCH_VRM = 'ABC1234';
    const DUMMY_SEARCH_VIN = '123445656643534';
    const DUMMY_CAN_REFUSE_TO_TEST = false;
    const DUMMY_IS_MOT_CONTINGENCY = true;
    const DUMMY_IS_MYSTERY_SHOPPER = true;

    public function testSetParametersWillReturnExpectedValues()
    {
        $viewModel = $this->getViewModel();

        $this->assertEquals(MotTestTypeCode::NORMAL_TEST, $viewModel->getMethod());
        $this->assertEquals(self::DUMMY_OBFUSCATED_VEHICLE_ID, $viewModel->getVehicleId());
        $this->assertEquals(self::DUMMY_OBFUSCATED_VEHICLE_ID, $viewModel->getObfuscatedVehicleId());
        $this->assertEquals(self::DUMMY_NO_REGISTRATION, $viewModel->isNoRegistration());
        $this->assertEquals(self::DUMMY_VEHICLE_SOURCE, $viewModel->getVehicleSource());
        $this->assertEquals(self::DUMMY_SEARCH_VIN, $viewModel->getSearchVin());
        $this->assertEquals(self::DUMMY_SEARCH_VRM, $viewModel->getSearchVrm());
        $this->assertEquals(self::DUMMY_SEARCH_VIN, $viewModel->getVin());
        $this->assertEquals(self::DUMMY_SEARCH_VRM, $viewModel->getRegistration());
        $this->assertEquals([ 'test' => 'test' ], $viewModel->getEligibilityNotices());

        $this->assertTrue($viewModel->isEligibleForRetest());
        $this->assertFalse($viewModel->isRetest());
        $this->assertTrue($viewModel->isNormalTest());
        $this->assertTrue($viewModel->isInProgressTestExists());
        $this->assertTrue($viewModel->isMotContingency());
        $this->assertTrue($viewModel->isMysteryShopper());
    }

    public function testStartTestActionReturnsStartTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();

        $this->assertEquals(
            '/start-test-confirmation/' . self::DUMMY_OBFUSCATED_VEHICLE_ID . '/' . self::DUMMY_NO_REGISTRATION,
            $viewModel->getConfirmActionUrl()
        );
    }

    public function testStartTrainingTestActionReturnsStartDemoTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMethod(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);

        $this->assertEquals(
            '/start-training-test-confirmation/' . self::DUMMY_OBFUSCATED_VEHICLE_ID . '/' . self::DUMMY_NO_REGISTRATION,
            $viewModel->getConfirmActionUrl()
        );
    }

    public function testStartNonMotTestActionReturnsStartNonMotTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMethod(MotTestTypeCode::NON_MOT_TEST);

        $this->assertEquals(
            '/start-non-mot-test-confirmation/' . self::DUMMY_OBFUSCATED_VEHICLE_ID . '/' . self::DUMMY_NO_REGISTRATION,
            $viewModel->getConfirmActionUrl()
        );
    }

    public function testCanRefuseToTestOnNormalTest()
    {
        $viewModel = $this->getViewModel();

        $viewModel->setInProgressTestExists(false);
        $viewModel->setCanRefuseToTest(true, true);

        $this->assertTrue($viewModel->canRefuseToTest());
    }

    public function testCanRefuseToTestOnRetestTest()
    {
        $viewModel = $this->getViewModel();

        $viewModel->setMethod('RT');
        $viewModel->setInProgressTestExists(false);
        $viewModel->setCanRefuseToTest(true, true);

        $this->assertTrue($viewModel->canRefuseToTest());
    }

    private function getViewModel()
    {
        $viewModel = new StartTestConfirmationViewModel();

        $viewModel->setMethod(MotTestTypeCode::NORMAL_TEST);
        $viewModel->setObfuscatedVehicleId(self::DUMMY_OBFUSCATED_VEHICLE_ID);
        $viewModel->setNoRegistration(self::DUMMY_NO_REGISTRATION);
        $viewModel->setVehicleSource(self::DUMMY_VEHICLE_SOURCE);
        $viewModel->setInProgressTestExists(self::DUMMY_IN_PROGRESS_TEST_EXISTS);
        $viewModel->setSearchVrm(self::DUMMY_SEARCH_VRM);
        $viewModel->setSearchVin(self::DUMMY_SEARCH_VIN);
        $viewModel->setRegistration(self::DUMMY_SEARCH_VRM);
        $viewModel->setVin(self::DUMMY_SEARCH_VIN);
        $viewModel->setEligibilityNotices([ 'test' => 'test' ]);
        $viewModel->setEligibleForRetest(true);
        $viewModel->setMotContingency(self::DUMMY_IS_MOT_CONTINGENCY);
        $viewModel->setIsMysteryShopper(self::DUMMY_IS_MYSTERY_SHOPPER);

        return $viewModel;
    }

}
