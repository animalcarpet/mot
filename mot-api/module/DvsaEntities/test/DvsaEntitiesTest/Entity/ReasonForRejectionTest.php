<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Entity\VehicleClass;
use PHPUnit_Framework_TestCase;

/**
 * Class ReasonForRejectionTest.
 *
 * TODO update test to change in entity
 */
class ReasonForRejectionTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $reasonForRejection = new ReasonForRejection();

        $this->assertNull(
            $reasonForRejection->getRfrId(),
            '"rfr id" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getTestItemSelectorId(),
            '"testItemSelectorId" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getInspectionManualReference(),
            '"inspection manual reference" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getMinorItem(),
            '"minor item" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getLocationMarker(),
            '"location marker" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getQtMarker(),
            '"QT marker" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getNote(),
            '"note" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getManual(),
            '"manual" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getSpecProc(),
            '"spec proc" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getTestItemSelector(),
            '"test item selector" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getSectionTestItemSelector(),
            '"section test item selector" should initially be null'
        );

        $this->assertNull(
            $reasonForRejection->getIsAdvisory(),
            '"is Advisory" should initially be null'
        );
        $this->assertNull(
            $reasonForRejection->getIsPrsFail(),
            '"is PRS Fail" should initially be null'
        );
    }

    public function testSetsPropertiesCorrectly()
    {
        $data = [
            'testItemSelectorName' => 'Stop Lamp',
            'inspectionManualReference' => '10',
            'minorItem' => 0,
            'description' => 'missing',
            'locationMarker' => 1,
            'qtMarker' => 1,
            'note' => 0,
            'manual' => '4',
            'specProc' => 0,
            'inspectionManualDescription' => 'a mandatory rear fog lamp tell-tale missing or inoperative',
            'advisoryText' => '',
            'testItemSelector' => '5012',
            'sectionTestItemSelector' => '5000',
        ];

        $testItemSelector = new TestItemSelector();
        $sectionTestItemSelector = new TestItemSelector();

        $reasonForRejection = new ReasonForRejection();
        $reasonForRejection->setTestItemSelectorName($data['testItemSelectorName'])
                            ->setInspectionManualReference($data['inspectionManualReference'])
                            ->setMinorItem($data['minorItem'])
                            ->setLocationMarker($data['locationMarker'])
                            ->setQtMarker($data['qtMarker'])
                            ->setNote($data['note'])
                            ->setManual($data['manual'])
                            ->setSpecProc($data['specProc'])
                            ->setTestItemSelector($testItemSelector)
                            ->setSectionTestItemSelector($sectionTestItemSelector);

        $this->assertEquals($data['testItemSelectorName'], $reasonForRejection->getTestItemSelectorName());
        $this->assertEquals($data['inspectionManualReference'], $reasonForRejection->getInspectionManualReference());
        $this->assertEquals($data['minorItem'], $reasonForRejection->getMinorItem());
        $this->assertEquals($data['locationMarker'], $reasonForRejection->getLocationMarker());
        $this->assertEquals($data['qtMarker'], $reasonForRejection->getQtMarker());
        $this->assertEquals($data['note'], $reasonForRejection->getNote());
        $this->assertEquals($data['manual'], $reasonForRejection->getManual());
        $this->assertEquals($data['specProc'], $reasonForRejection->getSpecProc());
        $this->assertEquals($testItemSelector, $reasonForRejection->getTestItemSelector());
        $this->assertEquals($sectionTestItemSelector, $reasonForRejection->getSectionTestItemSelector());
    }

    public function testIsApplicableToVehicleClassReturnsTrueForApplicableVehicleClass()
    {
        $vehicleClass2 = (new VehicleClass(VehicleClassCode::CLASS_2));
        $reasonForRejection = (new ReasonForRejection())
            ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_1)))
            ->addVehicleClass($vehicleClass2);
        $vehicleClass = (new VehicleClass(VehicleClassCode::CLASS_2));

        $result = $reasonForRejection->isApplicableToVehicleClass($vehicleClass);

        $this->assertEquals(true, $result);
    }

    public function testIsApplicableToVehicleClassReturnsFalseForWrongVehicleClass()
    {
        $reasonForRejection = (new ReasonForRejection())
            ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_1)))
            ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_2)));
        $vehicleClass = (new VehicleClass(VehicleClassCode::CLASS_4));

        $result = $reasonForRejection->isApplicableToVehicleClass($vehicleClass);

        $this->assertEquals(false, $result);
    }
}
