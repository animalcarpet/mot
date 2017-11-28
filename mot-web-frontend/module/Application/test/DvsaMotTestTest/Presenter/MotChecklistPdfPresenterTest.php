<?php

namespace Application\test\DvsaMotTestTest\Presenter;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Presenter\MotChecklistPdfPresenter;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use DvsaMotTestTest\TestHelper\Fixture;


class MotChecklistPdfPresenterTest extends \PHPUnit_Framework_TestCase
{
    /** @var $officialVehicleWeightSpec OfficialWeightSourceForVehicle|\PHPUnit_Framework_MockObject_MockObject */
    private $officialVehicleWeightSpec;

    /** @var $presenter MotChecklistPdfPresenter */
    private $presenter;
    /** @var $identity MotFrontendIdentityInterface|\PHPUnit_Framework_MockObject_MockObject  */
    private $identity;

    /** @var $vehicle DvsaVehicle */
    private $vehicle;

    /** @var $testDto MotTest */
    private $testDto;

    public function setUp()
    {
        $this->officialVehicleWeightSpec = XMock::of(OfficialWeightSourceForVehicle::class);

        $this->presenter = new MotChecklistPdfPresenter($this->officialVehicleWeightSpec);

        $this->identity = XMock::of(MotFrontendIdentityInterface::class);
        $this->presenter->setIdentity($this->identity);
    }

    /**
     * @dataProvider dataProviderTestFieldsAreDifferentForClass1And2
     *
     * @param int $classCode
     * @param int $fieldCount
     *
     * @throws \Exception
     */
    public function testFieldsAreDifferentForClass1And2($classCode, $fieldCount)
    {
        $this->setUpPresenterBasedOnClass($classCode);
        $this->assertCount($fieldCount, $this->presenter->getDataFields());
    }

    /**
     * @dataProvider dataProviderFeatureToggleStatus
     *
     * @param string $classCode
     */
    public function testPickVehicleWeight_withFeatureToggle($classCode)
    {
        $this->setUpPresenterBasedOnClass($classCode);

        $this->presenter->pickVehicleWeight();
    }


    public function dataProviderFeatureToggleStatus()
    {
        return [
            [VehicleClassCode::CLASS_4, true],
        ];
    }

    public function dataProviderTestFieldsAreDifferentForClass1And2()
    {
        return [
            [VehicleClassCode::CLASS_1, 10],
            [VehicleClassCode::CLASS_2, 10],
            [VehicleClassCode::CLASS_3, 11],
            [VehicleClassCode::CLASS_4, 11],
            [VehicleClassCode::CLASS_5, 11],
            [VehicleClassCode::CLASS_7, 11],
        ];
    }

    private function getDvsaVehicleStub($class)
    {
        if($class < 3){
            return new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));
        }
        else {
            return new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));
        }
    }
    private function getMotTestStub($class)
    {
        if($class < 3){
            return new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        }
        else {
            return new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        }
    }

    /**
     * @param $classCode
     */
    private function setUpPresenterBasedOnClass($classCode)
    {
        $this->vehicle = $this->getDvsaVehicleStub($classCode);
        $this->testDto = $this->getMotTestStub($classCode);

        $this->presenter->setMotTest($this->testDto);
        $this->presenter->setVehicle($this->vehicle);
    }
}
