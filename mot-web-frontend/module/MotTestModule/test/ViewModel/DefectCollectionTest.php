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

        $defectBreadcrumbParts = explode(">", $rawData['reasonsForRejection'][1]['testItemSelectorName']);
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
            $defectCategoryName . ' ' . $rawData['reasonsForRejection'][1]['description'],
            $testCollection->getDefects()[0]->getDescription()
        );

        $this->assertEquals(
            $defectCategoryName . ' ' . $rawData['reasonsForRejection'][1]['advisoryText'],
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
}