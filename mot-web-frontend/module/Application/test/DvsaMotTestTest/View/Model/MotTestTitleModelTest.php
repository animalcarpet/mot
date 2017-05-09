<?php

namespace DvsaMotTestTest\View\Model;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaMotTest\View\Model\MotTestTitleModel;
use DvsaMotTestTest\TestHelper\Fixture;

class MotTestTitleModelTest extends \PHPUnit_Framework_TestCase
{
    const MOT_TESTING_TITLE = 'MOT testing';
    const MOT_TRAINING_TEST_TITLE = 'Training test';

    /** @var MotTestTitleModel */
    private $motTestTitleModel;

    public function setUp()
    {
        $this->motTestTitleModel = new MotTestTitleModel();
    }

    public function testMotTestIsNotAnyTypeReturnsDefaultTitle()
    {
        $title = $this->motTestTitleModel->getTitle(null);
        $this->assertEquals(self::MOT_TESTING_TITLE, $title);
    }

    public function testMotTestIsNotTrainingReturnsDefaultTitle()
    {
        $motTestData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $motTestData->testTypeCode = MotTestTypeCode::NORMAL_TEST;

        $motTest = new MotTest($motTestData);
        $title = $this->motTestTitleModel->getTitle($motTest);
        $this->assertEquals(self::MOT_TESTING_TITLE, $title);
    }

    public function testMotTestIsTrainingReturnsTrainingTitle()
    {
        $motTestData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $motTestData->testTypeCode = MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;

        $motTest = new MotTest($motTestData);
        $title = $this->motTestTitleModel->getTitle($motTest);
        $this->assertEquals(self::MOT_TRAINING_TEST_TITLE, $title);
    }
}
