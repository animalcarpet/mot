<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategoryCollection;

/**
 * Class ComponentCategoryCollectionTest.
 */
class ComponentCategoryCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreationWithVe()
    {
        $testCollection = ComponentCategoryCollection::fromDataFromApi(
            $this->getTestItemSelectorsWithRfrs(), true
        );

        $this->assertEquals('two', $testCollection->getColumnCountForHtml());
        $this->assertEquals(2, count($testCollection->getColumns()));
        $this->assertEquals([0, 1], $testCollection->getCategoryPathIds());
        $this->assertInstanceOf(ComponentCategoryCollection::class, $testCollection);
        $this->assertTrue($testCollection->isVe());
        $this->assertEquals(2, count($testCollection->getCategoryPath()));
        $this->assertEquals('RFR name', $testCollection->getColumns()[0]->getColumnTitle());
        $this->assertEquals(3, count($testCollection->getColumns()[0]->getComponentCategories()));
    }

    public function testCreationWithoutVe()
    {
        $testCollection = ComponentCategoryCollection::fromDataFromApi(
            $this->getTestItemSelectorsWithRfrs(), false
        );

        $this->assertEquals('two', $testCollection->getColumnCountForHtml());
        $this->assertEquals(2, count($testCollection->getColumns()));
        $this->assertEquals([0, 1], $testCollection->getCategoryPathIds());
        $this->assertInstanceOf(ComponentCategoryCollection::class, $testCollection);
        $this->assertFalse($testCollection->isVe());
        $this->assertEquals(2, count($testCollection->getCategoryPath()));
        $this->assertEquals('RFR name', $testCollection->getColumns()[0]->getColumnTitle());
        $this->assertEquals(3, count($testCollection->getColumns()[0]->getComponentCategories()));
    }

    /**
     * @return array
     */
    private function getTestItemSelectorsWithRfrs()
    {
        return [
            [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 1,
                    'parentTestItemSelectorId' => 0,
                    'id' => 0,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
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
            ],
            [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 1,
                    'parentTestItemSelectorId' => 0,
                    'id' => 1,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
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
            ],
        ];
    }
}