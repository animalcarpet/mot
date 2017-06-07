<?php

namespace DvsaMotTestTest\ViewModel\MotTestLog;

use Dvsa\Mot\ApiClient\Request\TypeConversion\DateTimeConverter;
use Dvsa\Mot\ApiClient\Resource\Item\Colour;
use Dvsa\Mot\ApiClient\Resource\Item\FuelType;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaMotTest\Service\AuthorisedClassesService;
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
    const TEST_COUNTRY_OF_REGISTRATION = 'GB, UK, ENG, CYM, SCO (UK) - Great Britain';
    const TEST_CLASS_UNKNOWN = 'Unknown';
    const TEST_MAKE = 'CHRYSLER';
    const TEST_MODEL = 'HABANA';
    const TEST_MAKE_AND_MODEL = 'CHRYSLER, HABANA';
    const TEST_CLASS = '7';
    const TEST_BRAKE_TEST_WEIGHT = 1234;
    const TEST_PRIMARY_COLOUR = 'Beige';
    const TEST_SECONDARY_COLOUR = 'Black';
    const TEST_COMPOUND_COLOUR = 'Beige, Black';
    const TEST_FUEL_TYPE = 'Petrol';
    const TEST_ENGINE_CAPACITY = 1700;
    const TEST_EXPECTED_COMPOUND_ENGINE_DESCRIPTION = 'Petrol, 1700';

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
        $this->assertEquals(['test' => 'test'], $viewModel->getEligibilityNotices());
        $this->assertTrue($viewModel->isEligibleForRetest());
        $this->assertFalse($viewModel->isRetest());
        $this->assertTrue($viewModel->isNormalTest());
        $this->assertTrue($viewModel->isInProgressTestExists());
        $this->assertTrue($viewModel->isMotContingency());
        $this->assertTrue($viewModel->isMysteryShopper());
        $this->assertFalse($viewModel->isNoTestClassSetOnSubmission());
    }

    public function testStartTestActionReturnsStartTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();
        $this->assertEquals(
            '/start-test-confirmation/'.self::DUMMY_OBFUSCATED_VEHICLE_ID.'/'.self::DUMMY_NO_REGISTRATION,
            $viewModel->getConfirmActionUrl()
        );
    }

    public function testStartTrainingTestActionReturnsStartDemoTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMethod(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
        $this->assertEquals(
            '/start-training-test-confirmation/'.self::DUMMY_OBFUSCATED_VEHICLE_ID.'/'.self::DUMMY_NO_REGISTRATION,
            $viewModel->getConfirmActionUrl()
        );
    }

    public function testStartNonMotTestActionReturnsStartNonMotTestConfirmationActionUrl()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMethod(MotTestTypeCode::NON_MOT_TEST);
        $this->assertEquals(
            '/start-non-mot-test-confirmation/'.self::DUMMY_OBFUSCATED_VEHICLE_ID.'/'.self::DUMMY_NO_REGISTRATION,
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

    public function testIsInProgress()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMethod('DT');
        $this->assertTrue($viewModel->isTrainingTest());
    }

    public function testMakeAndModel()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMakeAndModel(self::TEST_MAKE, self::TEST_MODEL);
        $this->assertSame(self::TEST_MAKE_AND_MODEL, $viewModel->getMakeAndModel());
    }

    public function testShouldShowChangeLinks_whenNormalTest()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setInProgressTestExists(false);
        $viewModel->setMethod('NT');
        $this->assertTrue($viewModel->shouldShowChangeLinks());
    }

    public function testShouldShowChangeLinks_whenDemoTest()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setInProgressTestExists(false);
        $viewModel->setMethod('DT');
        $this->assertFalse($viewModel->shouldShowChangeLinks());
    }

    public function testTestClass()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass(self::TEST_CLASS);
        $this->assertSame(self::TEST_CLASS, $viewModel->getMotTestClass());
    }

    public function testIsClassUnset()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass(self::TEST_CLASS_UNKNOWN);
        $this->assertTrue($viewModel->isClassUnset());
    }

    public function testIsClassSet()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass(self::TEST_CLASS);
        $this->assertFalse($viewModel->isClassUnset());
    }

    public function testExpirationDate()
    {
        $viewModel = $this->getViewModel();
        $testExpirationDate = DateTimeConverter::dateTimeToString(new \DateTime());
        $viewModel->setMotExpirationDate($testExpirationDate);
        $this->assertSame(DateTimeDisplayFormat::textDate($testExpirationDate), $viewModel->getMotExpirationDate());
    }

    public function testCountryOfRegistration()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setCountryOfRegistration(self::TEST_COUNTRY_OF_REGISTRATION);
        $this->assertSame(self::TEST_COUNTRY_OF_REGISTRATION, $viewModel->getCountryOfRegistration());
    }

    public function testBrakeTestWeight()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setBrakeTestWeight(self::TEST_BRAKE_TEST_WEIGHT);
        $this->assertSame(self::TEST_BRAKE_TEST_WEIGHT, $viewModel->getBrakeTestWeight());
    }

    public function testFirstUsedDate()
    {
        $viewModel = $this->getViewModel();
        $testFirstUsedDate = DateTimeConverter::dateTimeToString(new \DateTime());
        $viewModel->setFirstUsedDate($testFirstUsedDate);
        $this->assertSame(DateTimeDisplayFormat::textDate($testFirstUsedDate), $viewModel->getFirstUsedDate());
    }

    public function testCompoundColour()
    {
        $viewModel = $this->getViewModel();
        $colour = new \stdClass();
        $colour->code = ColourCode::BEIGE;
        $colour->name = self::TEST_PRIMARY_COLOUR;
        $secondaryColour = new \stdClass();
        $secondaryColour->code = ColourCode::BLACK;
        $secondaryColour->name = self::TEST_SECONDARY_COLOUR;
        $viewModel->setCompoundedColour(new Colour($colour), new Colour($secondaryColour));
        $this->assertSame(self::TEST_COMPOUND_COLOUR, $viewModel->getCompoundedColour());
    }

    public function testCompoundColourSecondaryUnset()
    {
        $viewModel = $this->getViewModel();
        $colour = new \stdClass();
        $colour->code = ColourCode::BEIGE;
        $colour->name = self::TEST_PRIMARY_COLOUR;
        $secondaryColour = new \stdClass();
        $secondaryColour->code = ColourCode::NOT_STATED;
        $secondaryColour->name = 'Not Stated';
        $viewModel->setCompoundedColour(new Colour($colour), new Colour($secondaryColour));
        $this->assertSame(self::TEST_PRIMARY_COLOUR, $viewModel->getCompoundedColour());
    }

    public function testEngineDetails()
    {
        $viewModel = $this->getViewModel();
        $fuelType = new \stdClass();
        $fuelType->code = FuelTypeCode::PETROL;
        $fuelType->name = self::TEST_FUEL_TYPE;
        $viewModel->setEngine(new FuelType($fuelType), self::TEST_ENGINE_CAPACITY);
        $this->assertSame(self::TEST_EXPECTED_COMPOUND_ENGINE_DESCRIPTION, $viewModel->getEngine());
    }

    public function testIsPermittedToTestText_whenVtsAndTesterAreAuthorised()
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass('2');
        $viewModel->setIsPermittedToTestText($this->mockCombinedAuthorisedClasses($this->allTesterApprovedClasses(), $this->allVtsApprovedClasses()));
        $this->assertEmpty($viewModel->getIsPermittedToTestText());
    }

    /**
     * @dataProvider dataProviderVehicleTestClasses
     */
    public function testIsPermittedToTestText_whenBothVtsAndTesterAreNotAuthorised($testClass)
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass($testClass[0]);
        $viewModel->setIsPermittedToTestText($this->mockCombinedAuthorisedClasses([], []));
        $this->assertEquals(sprintf('This Vehicle Testing Station isn’t authorised to test class %s vehicles and you’re not qualified in this class.', $testClass[0]), $viewModel->getIsPermittedToTestText());
    }

    /**
     * @dataProvider dataProviderVehicleTestClasses
     */
    public function testIsPermittedToTestText_whenVtsNotPermitted($testClass)
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass($testClass[0]);
        $viewModel->setIsPermittedToTestText($this->mockCombinedAuthorisedClasses($this->allTesterApprovedClasses(), []));
        $this->assertEquals(sprintf('This Vehicle Testing Station isn’t authorised to test class %s vehicles.', $testClass[0]), $viewModel->getIsPermittedToTestText());
    }

    /**
     * @dataProvider dataProviderVehicleTestClasses
     */
    public function testIsPermittedToTestText_whenTesterNotAuthorised($testClass)
    {
        $viewModel = $this->getViewModel();
        $viewModel->setMotTestClass($testClass[0]);
        $viewModel->setIsPermittedToTestText($this->mockCombinedAuthorisedClasses([], $this->allVtsApprovedClasses()));
        $this->assertEquals(sprintf('You’re not qualified to test class %s vehicles.', $testClass[0]), $viewModel->getIsPermittedToTestText());
    }

    public function dataProviderVehicleTestClasses()
    {
        return [
            ['testClass' => ['1']],
            ['testClass' => ['2']],
            ['testClass' => ['3']],
            ['testClass' => ['4']],
            ['testClass' => ['5']],
            ['testClass' => ['7']],
        ];
    }

    private function mockCombinedAuthorisedClasses(array $testerApprovedClasses, array $vtsApprovedClasses)
    {
        return [
            AuthorisedClassesService::KEY_FOR_PERSON_APPROVED_CLASSES => $testerApprovedClasses,
            AuthorisedClassesService::KEY_FOR_VTS_APPROVED_CLASSES => $vtsApprovedClasses,
        ];
    }

    private function allTesterApprovedClasses()
    {
        return [
            '1',
            '2',
            '3',
            '4',
            '5',
            '7',
        ];
    }

    private function allVtsApprovedClasses()
    {
        return [
            '1',
            '2',
            '3',
            '4',
            '5',
            '7',
        ];
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
        $viewModel->setEligibilityNotices(['test' => 'test']);
        $viewModel->setEligibleForRetest(true);
        $viewModel->setMotContingency(self::DUMMY_IS_MOT_CONTINGENCY);
        $viewModel->setIsMysteryShopper(self::DUMMY_IS_MYSTERY_SHOPPER);
        $viewModel->setNoTestClassSetOnSubmission(false);

        return $viewModel;
    }
}
