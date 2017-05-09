<?php

namespace DvsaMotApiTest\Dto\Builders;

use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaMotApi\Dto\Builders\DefectDtoBuilder;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use PHPUnit_Framework_TestCase;

/**
 * Class DefectDtoBuilderTest.
 */
class DefectDtoBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DefectSentenceCaseConverter
     */
    private $mockDefectSentenceCaseConverter;
    /**
     * @var TestItemSelector
     */
    private $mockTestItemSelector;
    /**
     * @var ReasonForRejection
     */
    private $mockReasonForRejection;

    public function testDefectDtoBuilder()
    {
        $expectedData = [
            'id' => 666,
            'parentCategoryId' => 261,
            'description' => 'Chain guard fouling another component',
            'defectBreadcrumb' => 'Motorcycle drive system > Chain guard',
            'advisoryText' => 'Chain guard lightly rubbing',
            'inspectionManualReference' => '6.2.1c',
            'advisory' => true,
            'prs' => true,
            'failure' => false,
        ];
        $this->mockDefectSentenceCaseConverter = $this->mockDefectSentenceCaseConverter($expectedData);
        $this->mockTestItemSelector = $this->mockTestItemSelector($expectedData);
        $this->mockReasonForRejection = $this->mockReasonForRejection($expectedData, $this->mockTestItemSelector);

        $defectDtoBuilder = new DefectDtoBuilder($this->mockDefectSentenceCaseConverter);
        $defectDto = $defectDtoBuilder->fromEntity($this->mockReasonForRejection);
        $defectDtoJson = $defectDto->jsonSerialize();

        $this->assertEquals($expectedData, $defectDtoJson);
    }

    /**
     * @param array $expectedData
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDefectSentenceCaseConverter(array $expectedData)
    {
        $defectSentenceCaseConverterMock = $this
            ->getMockBuilder(DefectSentenceCaseConverter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = [
            'description' => $expectedData['description'],
            'advisoryText' => $expectedData['advisoryText'],
        ];

        $defectSentenceCaseConverterMock
            ->expects($this->any())
            ->method('getDefectDetailsForAddADefect')
            ->willReturn($result);

        return $defectSentenceCaseConverterMock;
    }

    /**
     * @param array $expectedData
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTestItemSelector(array $expectedData)
    {
        $testItemSelectorMock = $this
            ->getMockBuilder(TestItemSelector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testItemSelectorMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn($expectedData['parentCategoryId']);

        return $testItemSelectorMock;
    }

    /**
     * @param array            $expectedData
     * @param TestItemSelector $testItemSelectorMock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockReasonForRejection(array $expectedData, TestItemSelector $testItemSelectorMock)
    {
        $reasonForRejectionMock = $this
            ->getMockBuilder(ReasonForRejection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getRfrId')
            ->willReturn($expectedData['id']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getTestItemSelector')
            ->willReturn($testItemSelectorMock);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getTestItemSelectorName')
            ->willReturn($expectedData['defectBreadcrumb']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getInspectionManualReference')
            ->willReturn($expectedData['inspectionManualReference']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getIsAdvisory')
            ->willReturn($expectedData['advisory']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getIsPrsFail')
            ->willReturn($expectedData['prs']);

        return $reasonForRejectionMock;
    }
}
