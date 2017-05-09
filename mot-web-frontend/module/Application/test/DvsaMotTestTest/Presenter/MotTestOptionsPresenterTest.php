<?php

namespace DvsaMotTest\Presenter;

use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;
use DvsaCommon\Enum\MotTestTypeCode;

class MotTestOptionsPresenterTest extends \PHPUnit_Framework_TestCase
{
    public function testDisplayVehicleMakeAndModel()
    {
        $presenter = new MotTestOptionsPresenter(
            (new MotTestOptionsDto())
                ->setVehicleMake('make')
                ->setVehicleModel('model')
        );

        $this->assertEquals('make model', $presenter->displayVehicleMakeAndModel());
    }

    public function testDisplayVehicleRegistrationNumber()
    {
        $presenter = new MotTestOptionsPresenter(
            (new MotTestOptionsDto())
                ->setVehicleRegistrationNumber('registration number')
        );

        $this->assertEquals('registration number', $presenter->displayVehicleRegistrationNumber());
    }

    public function testDisplayMotTestStartedDate()
    {
        $presenter = new MotTestOptionsPresenter(
            (new MotTestOptionsDto())
                ->setMotTestStartedDate('2014-12-09T16:50:59Z')
        );

        $this->assertEquals('9 December 2014, 4:50pm', $presenter->displayMotTestStartedDate());
    }

    public function testIfMotTestTypeCodeReTestReturnTrueForIsMotTestReTest()
    {
        $presenter = $this->getPresenterWithTestOfType(MotTestTypeCode::RE_TEST);

        $this->assertTrue($presenter->isMotTestRetest());
        $this->assertFalse($presenter->isMotTest());
        $this->assertFalse($presenter->isNonMotTest());
    }

    public function testIfMotTestTypeCodeReTestReturnFalseForIsMotTestReTest()
    {
        $presenter = $this->getPresenterWithTestOfType(MotTestTypeCode::NORMAL_TEST);

        $this->assertTrue($presenter->isMotTest());
        $this->assertFalse($presenter->isMotTestRetest());
        $this->assertFalse($presenter->isNonMotTest());
    }

    public function testIfMotTestTypeCodeNonMotReturnTrueForIsNonMotTest()
    {
        $presenter = $this->getPresenterWithTestOfType(MotTestTypeCode::NON_MOT_TEST);

        $this->assertTrue($presenter->isNonMotTest());
        $this->assertFalse($presenter->isMotTestRetest());
        $this->assertFalse($presenter->isMotTest());
    }

    private function getPresenterWithTestOfType($type)
    {
        return new MotTestOptionsPresenter(
            (new MotTestOptionsDto())->setMotTestTypeDto(
                (new MotTestTypeDto())->setCode($type)
            )
        );
    }
}
