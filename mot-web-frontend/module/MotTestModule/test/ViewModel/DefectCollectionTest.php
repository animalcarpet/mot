<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;
use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;

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

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['rfrId'],
            $testCollection->getDefects()[0]->getDefectId()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['testItemSelectorId'],
            $testCollection->getDefects()[0]->getParentCategoryId()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['description'],
            $testCollection->getDefects()[0]->getDescription()
        );

        $this->assertEquals(
            $rawData['reasonsForRejection'][1]['advisoryText'],
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
     * @param ReasonForRejectionDto[] $dtos
     */
    public function testCreationFromSearchResults(array $dtos)
    {
        $testCollection = DefectCollection::fromSearchResult($dtos);
        $dto = $dtos[0];

        $this->assertInstanceOf(DefectCollection::class, $testCollection);

        $this->assertEquals(
            $dto->getRfrId(),
            $testCollection->getDefects()[0]->getDefectId()
        );

        $this->assertEquals(
            $dto->getTestItemSelectorId(),
            $testCollection->getDefects()[0]->getParentCategoryId()
        );

        $this->assertEquals(
            $dto->getInspectionManualReference(),
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
                            'deficiencyCategoryCode' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE,
                            'isPreEuDirective' => true,
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
        $dto = new ReasonForRejectionDto();
        $dto->setRfrId(1);
        $dto->setTestItemSelectorId(2);
        $dto->setDescription("Description");
        $dto->setTestItemSelectorName("Hello");
        $dto->setAdvisoryText("Advisory");
        $dto->setInspectionManualReference("2.1.23");
        $dto->setInspectionManualReferenceUrl("url");
        $dto->setIsAdvisory(true);
        $dto->setIsPrsFail(false);
        $dto->setDeficiencyCategoryCode(RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE);
        $dto->setIsPreEuDirective(true);

        return [
            [
                [$dto]
            ]
        ];
    }
}
