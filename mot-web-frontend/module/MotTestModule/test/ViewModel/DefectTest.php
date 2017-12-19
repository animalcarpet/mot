<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;

class DefectTest extends \PHPUnit_Framework_TestCase
{
    const DEFECT_ID = 10;
    const PARENT_CATEGORY_ID = 20;
    const DESCRIPTION = 'Description';
    const ADVISORY_TEXT = 'Advisory text';
    const DEFAULT_BREADCRUMBS = 'asd';
    const INSPECTION_MANUAL_REFERENCE = 'Inspection manual reference';
    const INSPECTION_MANUAL_REFERENCE_URL = 'http://noot.com';
    const NEW_BREADCRUMBS = 'breadcrumb';

    /**
     * @dataProvider testCreationDP
     */
    public function testCreation($isAdvisory, $isPrs, $isFailure, $deficiencyCategory)
    {
        $defect = new Defect(
            self::DEFECT_ID,
            self::PARENT_CATEGORY_ID,
            self::DESCRIPTION,
            self::DEFAULT_BREADCRUMBS,
            self::ADVISORY_TEXT,
            self::INSPECTION_MANUAL_REFERENCE,
            $isAdvisory,
            $isPrs,
            $isFailure,
            $deficiencyCategory,
            $deficiencyCategory === RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE
        );

        $defect->setInspectionManualReferenceUrl(self::INSPECTION_MANUAL_REFERENCE_URL);
        $defect->setDefectBreadcrumb(self::NEW_BREADCRUMBS);

        $this->assertEquals(self::NEW_BREADCRUMBS, $defect->getDefectBreadcrumb());
        $this->assertEquals(self::DEFECT_ID, $defect->getDefectId());
        $this->assertEquals(self::PARENT_CATEGORY_ID, $defect->getParentCategoryId());
        $this->assertEquals(self::DESCRIPTION, $defect->getDescription());
        $this->assertEquals(self::ADVISORY_TEXT, $defect->getAdvisoryText());
        $this->assertEquals(self::INSPECTION_MANUAL_REFERENCE, $defect->getInspectionManualReference());
        $this->assertEquals(self::INSPECTION_MANUAL_REFERENCE_URL, $defect->getInspectionManualReferenceUrl());

        $this->assertEquals($isAdvisory, $defect->isAdvisory());
        $this->assertEquals($isPrs, $defect->isPrs());
        $this->assertEquals($isFailure, $defect->isFailure());

        $this->assertEquals($deficiencyCategory, $defect->getDeficiencyCategoryCode());
        $this->assertEquals($deficiencyCategory === RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, $defect->isPreEuDirective());

        $isMinor = $deficiencyCategory === RfrDeficiencyCategoryCode::MINOR;
        $this->assertEquals($isMinor, $defect->isMinorDefect());

        $this->assertEquals($isFailure, $defect->canDisplayFailureButton());
        $this->assertEquals($isPrs && !$isMinor, $defect->canDisplayPrsButton());
        $this->assertEquals($isAdvisory, $defect->canDisplayAdvisoryButton());
    }

    public function testCreationDP()
    {
        return [
            ['isAdvisory' => true, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => true, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => true, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => true, 'isPrs' => true, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            ['isAdvisory' => false, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => false, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => false, 'isPrs' => true, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => false, 'isPrs' => true, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => false, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => true, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::DANGEROUS],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => true, 'isPrs' => false, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],

            //["isAdvisory" => true, "isPrs" => true, "isFailure" => false, "deficiencyCategory" => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE],
            //["isAdvisory" => true, "isPrs" => true, "isFailure" => false, "deficiencyCategory" => RfrDeficiencyCategoryCode::DANGEROUS],
            //["isAdvisory" => true, "isPrs" => true, "isFailure" => false, "deficiencyCategory" => RfrDeficiencyCategoryCode::MAJOR],
            ['isAdvisory' => true, 'isPrs' => true, 'isFailure' => false, 'deficiencyCategory' => RfrDeficiencyCategoryCode::MINOR],
        ];
    }
}
