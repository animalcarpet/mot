<?php

namespace DvsaMotApiTest\Formatting;

use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionDescription;
use DvsaEntities\Entity\TestItemCategoryDescription;
use DvsaEntities\Entity\TestItemSelector;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use PHPUnit_Framework_TestCase;

/**
 * Class DefectSentenceCaseConverterTest.
 */
class DefectSentenceCaseConverterTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestReasonForRejection */
    private $motTestReasonForRejection;

    /** @var ReasonForRejection */
    private $reasonForRejection;

    /** @var TestItemCategoryDescription */
    private $testItemCategoryDescription;

    /** @var TestItemSelector */
    private $testItemSelector;

    /** @var Language */
    private $language;

    /** @var ReasonForRejectionDescription */
    private $reasonForRejectionDescription;

    /** @var DefectSentenceCaseConverter */
    private $defectSentenceCaseConverterService;

    public function setup()
    {
        // Create mocks
        $this->language = $this
            ->getMockBuilder(Language::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->motTestReasonForRejection = $this
            ->getMockBuilder(MotTestReasonForRejection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reasonForRejection = $this
            ->getMockBuilder(ReasonForRejection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testItemCategoryDescription = $this
            ->getMockBuilder(TestItemCategoryDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testItemSelector = $this
            ->getMockBuilder(TestItemSelector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reasonForRejectionDescription = $this
            ->getMockBuilder(ReasonForRejectionDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock methods
        $this
            ->motTestReasonForRejection
            ->method('getReasonForRejection')
            ->willReturn($this->reasonForRejection);

        $this
            ->reasonForRejection
            ->method('getTestItemSelector')
            ->willReturn($this->testItemSelector);

        $this
            ->testItemCategoryDescription
            ->method('getLanguage')
            ->willReturn($this->language);

        $categoryDescriptions[] = $this->testItemCategoryDescription;
        $this
            ->testItemSelector
            ->method('getDescriptions')
            ->willReturn($categoryDescriptions);

        $this
            ->reasonForRejection
            ->method('getLanguage')
            ->willReturn($this->language);

        $this
            ->reasonForRejectionDescription
            ->method('getLanguage')
            ->willReturn($this->language);

        $rfrDescriptions[] = $this->reasonForRejectionDescription;
        $this
            ->reasonForRejection
            ->method('getDescriptions')
            ->willReturn($rfrDescriptions);

        $this->defectSentenceCaseConverterService = new DefectSentenceCaseConverter();
    }

    public function testAbsIsCapitalised()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Abs category';
        $expectedString = 'Anti-lock braking system category';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testSrsIsCapitalised()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Category for srs';
        $expectedString = 'Category for supplementary restraint system';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testWhitespaceWithAcronymExpansion()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = ' HMRC SRS RAC  ';
        $expectedString = 'HMRC supplementary restraint system RAC';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsExpandedOnlyOnce()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'SRS SRS VIN VIN';
        $expectedString = 'Supplementary restraint system SRS vehicle identification number VIN';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testUpperCaseAcronymsInApostrophes()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Damage to zone \'A\'';
        $expectedString = 'Damage to zone \'A\'';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInAddADefect()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInSearch()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForSearchForADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInDefectCategories()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForDefectCategories($stringToCovert, $expectedString);
    }

    public function testAddADefect()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);

        $this->setTestItemCategoryDescriptionName('Category name');
        $this->setTestItemCategoryDescription('Category description');
        $this->setReasonForRejectionName('Defect name');
        $this->setReasonForRejectionAdvisoryText('Defect advisory text');
        $expectedResult = [
            'description' => 'Category description defect name',
            'advisoryText' => 'Category description defect advisory text',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForAddADefect($this->reasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testListAndSearch()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);

        $this->setTestItemCategoryDescriptionName('Category name');
        $this->setTestItemCategoryDescription('Category description');
        $this->setReasonForRejectionName('Defect name');
        $this->setReasonForRejectionAdvisoryText('Defect advisory text');
        $this->setReasonForRejectionInspectionManualDescription('Inspection manual description');
        $expectedResult = [
            'description' => 'Category description defect name',
            'advisoryText' => 'Category description defect advisory text',
            'inspectionManualDescription' => 'Inspection manual description',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForListAndSearch($this->reasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testTestResultsAndBasket()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);

        $this->setTestItemCategoryDescriptionName('Category name');
        $this->setTestItemCategoryDescription('Category description');
        $this->setReasonForRejectionName('Defect name');
        $this->setReasonForRejectionAdvisoryText('Defect advisory text');
        $this->setReasonForRejectionInspectionManualDescription('Inspection manual description');
        $expectedResult = [
            'name' => 'Category name',
            'failureText' => 'defect name',
            'testItemSelectorDescription' => 'Category description',
            'nameCy' => '',
            'failureTextCy' => '',
            'testItemSelectorDescriptionCy' => '',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForTestResultsAndBasket($this->motTestReasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testAcronymsExpandedInResultsBasketWithAdvisory()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $this->setDefectType(ReasonForRejectionTypeName::ADVISORY);
        $this->setTestItemCategoryDescription('Abs');
        $this->setReasonForRejectionAdvisoryText('warning lamp indicates an ABS fault (advisory text)');
        $this->setTestItemCategoryDescriptionName('Abs');
        $expectedResult = [
            'name' => 'Abs',
            'failureText' => 'warning lamp indicates an ABS fault (advisory text)',
            'testItemSelectorDescription' => 'Anti-lock braking system',
            'nameCy' => '',
            'failureTextCy' => '',
            'testItemSelectorDescriptionCy' => '',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForTestResultsAndBasket($this->motTestReasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testAcronymsExpandedInResultsBasketWithPrs()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);
        $this->setDefectType(ReasonForRejectionTypeName::PRS);
        $this->setTestItemCategoryDescription('Abs');
        $this->setReasonForRejectionName('warning lamp indicates an ABS fault (PRS text)');
        $this->setTestItemCategoryDescriptionName('Abs');
        $expectedResult = [
            'name' => 'Abs',
            'failureText' => 'warning lamp indicates an ABS fault (PRS text)',
            'testItemSelectorDescription' => 'Anti-lock braking system',
            'nameCy' => '',
            'failureTextCy' => '',
            'testItemSelectorDescriptionCy' => '',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForTestResultsAndBasket($this->motTestReasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testDefectCategories()
    {
        $this->setLanguage(LanguageTypeCode::ENGLISH);

        $this->setTestItemCategoryDescriptionName('category name');
        $this->setTestItemCategoryDescription('Category description');
        $this->setReasonForRejectionName('Defect name');
        $this->setReasonForRejectionAdvisoryText('Defect advisory text');
        $this->setReasonForRejectionInspectionManualDescription('Inspection manual description');
        $expectedResult = [
            'name' => 'Category name',
            'description' => 'Category description',
            'nameCy' => '',
            'descriptionCy' => '',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDetailsForDefectCategories($this->testItemSelector);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testWelshDefectCategories()
    {
        $this->setLanguage(LanguageTypeCode::WELSH);

        $this->setTestItemCategoryDescriptionName('Welsh category name');
        $this->setTestItemCategoryDescription('Welsh category description');
        $expectedResult = [
            'name' => '',
            'description' => '',
            'nameCy' => 'Welsh category name',
            'descriptionCy' => 'Welsh category description',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDetailsForDefectCategories($this->testItemSelector);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testWelshDefectDetails()
    {
        $this->setLanguage(LanguageTypeCode::WELSH);
        $this->setDefectType(ReasonForRejectionTypeName::ADVISORY);
        $this->setTestItemCategoryDescription('Welsh category description');
        $this->setReasonForRejectionAdvisoryText('Welsh advisory text');
        $this->setTestItemCategoryDescriptionName('Welsh category name');
        $expectedResult = [
            'name' => '',
            'failureText' => '',
            'testItemSelectorDescription' => '',
            'nameCy' => 'Welsh category name',
            'failureTextCy' => 'Welsh advisory text',
            'testItemSelectorDescriptionCy' => 'Welsh category description',
        ];
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForTestResultsAndBasket($this->motTestReasonForRejection);

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function setTestItemCategoryDescription($description)
    {
        $this
            ->testItemCategoryDescription
            ->method('getDescription')
            ->willReturn($description);
    }

    private function setReasonForRejectionAdvisoryText($advisoryText)
    {
        $this
            ->reasonForRejectionDescription
            ->method('getAdvisoryText')
            ->willReturn($advisoryText);
    }

    private function setReasonForRejectionInspectionManualDescription($inspectionManualDescription)
    {
        $this
            ->reasonForRejectionDescription
            ->method('getInspectionManualDescription')
            ->willReturn($inspectionManualDescription);
    }

    private function setReasonForRejectionName($name)
    {
        $this
            ->reasonForRejectionDescription
            ->method('getName')
            ->willReturn($name);
    }

    private function assertConversionForAddADefect($stringToConvert, $expectedString)
    {
        $this->setTestItemCategoryDescription($stringToConvert);
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForAddADefect($this->reasonForRejection);

        $this->assertEquals($expectedString, $actualResult['description']);
    }

    private function assertConversionForDefectCategories($stringToConvert, $expectedString)
    {
        $this->setTestItemCategoryDescription($stringToConvert);
        $actualResult = $this->defectSentenceCaseConverterService->getDetailsForDefectCategories($this->testItemSelector);

        $this->assertEquals($expectedString, $actualResult['description']);
    }

    private function assertConversionForSearchForADefect($stringToConvert, $expectedString)
    {
        $this->setTestItemCategoryDescription($stringToConvert);
        $actualResult = $this->defectSentenceCaseConverterService->getDefectDetailsForListAndSearch($this->reasonForRejection);

        $this->assertEquals($expectedString, $actualResult['description']);
    }

    private function setDefectType($type)
    {
        $this
            ->motTestReasonForRejection
            ->method('getType')
            ->willReturn($type);
    }

    private function setTestItemCategoryDescriptionName($name)
    {
        $this
            ->testItemCategoryDescription
            ->method('getName')
            ->willReturn($name);
    }

    private function setLanguage($langaugeTypeCode)
    {
        $this
            ->testItemCategoryDescription
            ->method('getCode')
            ->willReturn($langaugeTypeCode);

        $this
            ->language
            ->method('getCode')
            ->willReturn($langaugeTypeCode);
    }
}
