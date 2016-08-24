<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;

class DefectCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider creationDataProvider
     *
     * @param array $rawData
     */
    public function testCreation(array $rawData)
    {
        $testCollection = DefectCollection::fromDataFromApi($rawData);

        $defectBreadcrumbParts = explode('>', $rawData['reasonsForRejection'][1]['testItemSelectorName']);
        $defectCategoryName = end($defectBreadcrumbParts);

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['rfrId'],
            $testCollection->getDefects()[0]->getDefectId()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['testItemSelectorId'],
            $testCollection->getDefects()[0]->getParentCategoryId()
        );

        $this->assertEquals(
            $defectCategoryName.' '.$rawData['reasonsForRejection'][1]['description'],
            $testCollection->getDefects()[0]->getDescription()
        );

        $this->assertEquals(
            $defectCategoryName.' '.$rawData['reasonsForRejection'][1]['advisoryText'],
            $testCollection->getDefects()[0]->getAdvisoryText()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['inspectionManualReference'],
            $testCollection->getDefects()[0]->getInspectionManualReference()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['isAdvisory'],
            $testCollection->getDefects()[0]->isAdvisory()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['isPrsFail'],
            $testCollection->getDefects()[0]->isPrs()
        );
    }

    /**
     * @dataProvider creationFromSearchResultsDataProvider
     *
     * @param array $rawData
     */
    public function testCreationFromSearchResults(array $rawData)
    {
        $testCollection = DefectCollection::fromSearchResults($rawData);
        $rawData = $rawData['data']['reasonsForRejection'][0];

        $this->assertInstanceOf(DefectCollection::class, $testCollection);

        $this->assertEquals(
            $rawData['rfrId'],
            $testCollection->getDefects()[0]->getDefectId()
        );

        $this->assertEquals(
            $rawData['testItemSelectorId'],
            $testCollection->getDefects()[0]->getParentCategoryId()
        );

        $this->assertEquals(
            $rawData['inspectionManualReference'],
            $testCollection->getDefects()[0]->getInspectionManualReference()
        );
    }

    /**
     * @return array
     */
    public function creationDataProvider()
    {
        return [
            [
                [
                    'testItemSelector' => [
                      'name' => 'Hello',
                    ],
                    'reasonsForRejection' => [
                        1 => [
                            'rfrId' => 1,
                            'testItemSelectorId' => 2,
                            'testItemSelectorName' => 'Hello',
                            'description' => 'Description',
                            'advisoryText' => 'Asde',
                            'inspectionManualReference' => '2.1.23',
                            'isAdvisory' => true,
                            'isPrsFail' => false,
                            'canBeDangerous' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function creationFromSearchResultsDataProvider()
    {
        return [
            [
                [
                'data' => [
                    'reasonsForRejection' => [
                        [
                            'rfrId' => 10006,
                            'testItemSelectorId' => 10000,
                            'testItemSelectorName' => 'Vehicle > ',
                            'inspectionManualReference' => '',
                            'minorItem' => true,
                            'locationMarker' => false,
                            'qtMarker' => false,
                            'note' => false,
                            'manual' => '',
                            'specProc' => false,
                            'isAdvisory' => true,
                            'isPrsFail' => false,
                            'canBeDangerous' => false,
                            'audience' => 'b',
                            'endDate' => null,
                            'vehicleClasses' => [],
                            'testItemSelector' => null,
                            'sectionTestItemSelector' => null,
                            'description' => 'Non obligatory mirror damaged',
                            'advisoryText' => 'Non obligatory mirror damaged',
                            'inspectionManualDescription' => '',
                        ],
                    ],
                ],
                ],
            ],
        ];
    }
}
