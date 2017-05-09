<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategory;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;

class ComponentCategoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $componentCategory = new ComponentCategory(
            20,
            30,
            40,
            'Name',
            'Description',
            ['Description 1', 'Description 2'],
            ['3', '4', '5'],
            DefectCollection::fromDataFromApi(
                [
                    'testItemSelector' => [
                        'name' => 'ParentCategoryName',
                    ],
                    'reasonsForRejection' => [
                        1 => [
                            'rfrId' => 1,
                            'testItemSelectorId' => 1,
                            'testItemSelectorName' => 'asd',
                            'description' => 'asd',
                            'advisoryText' => 'asd',
                            'inspectionManualReference' => '2.1.2',
                            'isAdvisory' => true,
                            'isPrsFail' => false,
                            'canBeDangerous' => true,
                        ],
                    ],
                ]));

        $this->assertEquals(20, $componentCategory->getRootCategoryId());
        $this->assertEquals(30, $componentCategory->getParentCategoryId());
        $this->assertEquals(40, $componentCategory->getCategoryId());
        $this->assertEquals('Name', $componentCategory->getName());
        $this->assertEquals('Description', $componentCategory->getDescription());
        $this->assertEquals(['Description 1', 'Description 2'], $componentCategory->getDescriptions());
        $this->assertEquals(['3', '4', '5'], $componentCategory->getVehicleClasses());
    }
}
