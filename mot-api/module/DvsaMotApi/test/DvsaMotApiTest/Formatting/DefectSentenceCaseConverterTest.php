<?php

namespace DvsaMotApiTest\Formatting;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionDescription;
use DvsaEntities\Entity\TestItemCategoryDescription;
use DvsaEntities\Entity\TestItemSelector;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;

/**
 * Class DefectSentenceCaseConverterTest.
 */
class DefectSentenceCaseConverterTest extends PHPUnit_Framework_TestCase
{
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

    /** @var FeatureToggles */
    private $featureToggles;

    public function setup()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);

        $this->defectSentenceCaseConverterService = new DefectSentenceCaseConverter($this->featureToggles);

        $this->language = $this
            ->getMockBuilder(Language::class)
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

        $this
            ->reasonForRejection
            ->method('getTestItemSelector')
            ->willReturn($this->testItemSelector);

        $this
            ->testItemCategoryDescription
            ->method('getLanguage')
            ->willReturn($this->language);

        $this
            ->testItemCategoryDescription
            ->method('getCode')
            ->willReturn(LanguageTypeCode::ENGLISH);

        $categoryDescriptions[] = $this->testItemCategoryDescription;
        $this
            ->testItemSelector
            ->method('getDescriptions')
            ->willReturn($categoryDescriptions);

        $this
            ->language
            ->method('getCode')
            ->willReturn(LanguageTypeCode::ENGLISH);

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
    }

    public function testConversionOfStringWithAcronymAtStart()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'TEST that a string with an acronym at the start is converted correctly';
        $expectedString = 'TEST that a string with an acronym at the start is converted correctly';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAbsIsCapitalised()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Abs category';
        $expectedString = 'Anti-lock braking system category';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testSrsIsCapitalised()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Category for srs';
        $expectedString = 'Category for supplementary restraint system';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testWhitespaceWithAcronymExpansion()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = ' HMRC SRS RAC  ';
        $expectedString = 'HMRC supplementary restraint system RAC';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsExpandedOnlyOnce()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'SRS SRS VIN VIN';
        $expectedString = 'Supplementary restraint system SRS vehicle identification number VIN';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testUpperCaseAcronymsInApostrophes()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Damage to zone \'A\'';
        $expectedString = 'Damage to zone \'A\'';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInAddADefect()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForAddADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInSearch()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForSearchForADefect($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInDefectCategories()
    {
        $this->enableTestResultEntryImprovements(true);
        $stringToCovert = 'Vehicle identification number VIN';
        $expectedString = 'Vehicle identification number VIN';

        $this->assertConversionForDefectCategories($stringToCovert, $expectedString);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInResultsBasketAsAdvisory()
    {
        $this->enableTestResultEntryImprovements(true);
        $categoryDescription = 'Abs';
        $advisoryText = 'warning lamp indicates an ABS fault';
        $expectedCategoryDescription = 'Anti-lock braking system';
        $expectedAdvisoryText = 'warning lamp indicates an ABS fault';

        $this->assertConversionForTestResultsWithAnAdvisoryDefect($categoryDescription, $advisoryText, $expectedCategoryDescription, $expectedAdvisoryText);
    }

    public function testAcronymsNotExpandedIfExpandedFormAlreadyPresentAsUsedInResultsBasketAsPrs()
    {
        $this->enableTestResultEntryImprovements(true);
        $categoryDescription = 'Abs';
        $name = 'warning lamp indicates an ABS fault';
        $expectedCategoryDescription = 'Anti-lock braking system';
        $expectedAdvisoryText = 'warning lamp indicates an ABS fault';

        $this->assertConversionForTestResultsWithAPrsDefect($categoryDescription, $name, $expectedCategoryDescription, $expectedAdvisoryText);
    }

    public function testToggleOffBehaviourForAcronymsInResultsBasket()
    {
        $this->enableTestResultEntryImprovements(false);
        $categoryDescription = 'Abs';
        $name = 'warning lamp indicates an ABS fault';
        $expectedCategoryDescription = 'Abs';
        $expectedAdvisoryText = 'warning lamp indicates an ABS fault';

        $this->assertConversionForTestResultsWithAPrsDefect($categoryDescription, $name, $expectedCategoryDescription, $expectedAdvisoryText);
    }

    public function testToggleOffBehaviourForInconsistentlyNamedDefectsInResultsBasket()
    {
        $this->enableTestResultEntryImprovements(false);
        $categoryDescription = 'Child Seat';
        $name = 'fitted not allowing full inspection of adult belt';
        $expectedCategoryDescription = 'Child Seat';
        $expectedAdvisoryText = 'fitted not allowing full inspection of adult belt';

        $this->assertConversionForTestResultsWithAPrsDefect($categoryDescription, $name, $expectedCategoryDescription, $expectedAdvisoryText);
    }

    public function testToggleOffBehaviourForExpandedAcronymsInResultsBasket()
    {
        $this->enableTestResultEntryImprovements(false);
        $categoryDescription = 'Vehicle Identification Number';
        $name = 'not permanently displayed ';
        $expectedCategoryDescription = 'Vehicle Identification Number';
        $expectedAdvisoryText = 'not permanently displayed ';

        $this->assertConversionForTestResultsWithAPrsDefect($categoryDescription, $name, $expectedCategoryDescription, $expectedAdvisoryText);
    }

    public function testAcronymsNotFormattedIfToggleOffInSearch()
    {
        $this->enableTestResultEntryImprovements(false);
        $stringToConvert = 'Abs';
        $expectedString = 'Abs';

        $this->setReasonForRejectionName($stringToConvert);
        $this->setTestItemCategoryDescription($stringToConvert);
        $actualResult = $this->defectSentenceCaseConverterService->formatRfrDescriptionsForDefectsAndSearchForADefect($this->reasonForRejection);
        $expectedResult = [
            'description' => $expectedString,
            'advisoryText' => null,
            'inspectionManualDescription' => null,
        ];

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
        $actualResult = $this->defectSentenceCaseConverterService->formatRfrDescriptionsForAddADefect($this->reasonForRejection);
        $expectedResult = [
            'description' => $expectedString,
            'advisoryText' => $expectedString,
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function assertConversionForDefectCategories($stringToConvert, $expectedString)
    {
        $this->setTestItemCategoryDescription($stringToConvert);
        $defectDescriptions = [];
        $actualResult = $this->defectSentenceCaseConverterService->formatTisDescriptionsForDefectCategories($defectDescriptions, $this->testItemSelector);
        $expectedResult = [
            'description' => $expectedString,
            'name' => '',
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function assertConversionForTestResultsWithAnAdvisoryDefect($categoryDescription, $advisoryText, $expectedCategoryDescription, $expectedAdvisoryText)
    {
        $this->setTestItemCategoryDescription($categoryDescription);
        $this->setReasonForRejectionAdvisoryText($advisoryText);

        $actualResult = $this->defectSentenceCaseConverterService->formatRfrDescriptionsForTestResultsAndBasket($this->reasonForRejection, ReasonForRejectionTypeName::ADVISORY);
        $expectedResult = [
            'testItemSelectorDescription' => $expectedCategoryDescription,
            'failureText' => $expectedAdvisoryText,
            'name' => null
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function assertConversionForTestResultsWithAPrsDefect($categoryDescription, $name, $expectedCategoryDescription, $expectedAdvisoryText)
    {
        $this->setTestItemCategoryDescription($categoryDescription);
        $this->setReasonForRejectionName($name);

        $actualResult = $this->defectSentenceCaseConverterService->formatRfrDescriptionsForTestResultsAndBasket($this->reasonForRejection, ReasonForRejectionTypeName::PRS);
        $expectedResult = [
            'testItemSelectorDescription' => $expectedCategoryDescription,
            'failureText' => $expectedAdvisoryText,
            'name' => null,
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function assertConversionForSearchForADefect($stringToConvert, $expectedString)
    {
        $this->setTestItemCategoryDescription($stringToConvert);
        $actualResult = $this->defectSentenceCaseConverterService->formatRfrDescriptionsForDefectsAndSearchForADefect($this->reasonForRejection);
        $expectedResult = [
            'description' => $expectedString,
            'advisoryText' => $expectedString,
            'inspectionManualDescription' => null,
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }

    private function enableTestResultEntryImprovements($boolean)
    {
        $this->featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)
            ->willReturn($boolean);
    }
}
