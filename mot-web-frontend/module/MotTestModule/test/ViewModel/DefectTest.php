<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;

class DefectTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $defect = new Defect(
            10,
            20,
            'Description',
            'asd',
            'Advisory text',
            'Inspection manual reference',
            true,
            false,
            true
        );

        $defect->setInspectionManualReferenceUrl('http://noot.com');

        $this->assertEquals(10, $defect->getDefectId());
        $this->assertEquals(20, $defect->getParentCategoryId());
        $this->assertEquals('Description', $defect->getDescription());
        $this->assertEquals('Advisory text', $defect->getAdvisoryText());
        $this->assertEquals('Inspection manual reference', $defect->getInspectionManualReference());
        $this->assertEquals(true, $defect->isAdvisory());
        $this->assertEquals(false, $defect->isPrs());
        $this->assertEquals(true, $defect->isFailure());
        $this->assertEquals('http://noot.com', $defect->getInspectionManualReferenceUrl());
    }
}
