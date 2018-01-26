<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;

class Defect
{
    const FAILURE_LABEL_TEXT = 'Failure';
    const DANGEROUS_LABEL_TEXT = 'Dangerous';
    const MAJOR_LABEL_TEXT = 'Major';
    const MINOR_LABEL_TEXT = 'Minor';
    /**
     * @var int
     */
    private $defectId;

    /**
     * @var int
     */
    private $parentCategoryId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $defectBreadcrumb;

    /**
     * @var string
     */
    private $advisoryText;

    /**
     * @var string
     */
    private $inspectionManualReference;

    /**
     * @var bool
     */
    private $advisory;

    /**
     * @var bool
     */
    private $prs;

    /**
     * @var bool
     */
    private $failure;

    /**
     * @var string
     */
    private $deficiencyCategoryCode;

    /**
     * @var bool
     */
    private $preEuDirective;

    /**
     * @var string
     */
    private $inspectionManualReferenceUrl;

    /**
     * Defect constructor.
     *
     * @param int    $defectId
     * @param int    $parentCategoryId
     * @param string $description
     * @param string $defectBreadcrumb
     * @param string $advisoryText
     * @param string $inspectionManualReference
     * @param bool   $isAdvisory
     * @param bool   $isPrs
     * @param bool   $isFailure
     * @param string $deficiencyCategoryCode
     * @param bool   $isPreEuDirective
     */
    public function __construct(
        $defectId,
        $parentCategoryId,
        $description,
        $defectBreadcrumb,
        $advisoryText,
        $inspectionManualReference,
        $isAdvisory,
        $isPrs,
        $isFailure,
        $deficiencyCategoryCode,
        $isPreEuDirective
    ) {
        $this->defectId = $defectId;
        $this->parentCategoryId = $parentCategoryId;
        $this->description = $description;
        $this->defectBreadcrumb = $defectBreadcrumb;
        $this->advisoryText = $advisoryText;
        $this->inspectionManualReference = $inspectionManualReference;
        $this->advisory = $isAdvisory;
        $this->prs = $isPrs;
        $this->failure = $isFailure;
        $this->deficiencyCategoryCode = $deficiencyCategoryCode;
        $this->preEuDirective = $isPreEuDirective;
    }

    /**
     * @param DefectDto $data
     *
     * @return Defect
     */
    public static function fromApi(DefectDto $data)
    {
        $defectId = $data->getId();
        $parentCategoryId = $data->getParentCategoryId();
        $description = $data->getDescription();
        $defectBreadcrumb = $data->getDefectBreadcrumb();
        $advisoryText = $data->getAdvisoryText();
        $inspectionManualReference = $data->getInspectionManualReference();
        $isAdvisory = $data->isAdvisory();
        $isPrs = $data->isPrs();
        $isFailure = $data->isFailure();
        $deficiencyCategoryCode = $data->getDeficiencyCategoryCode();
        $isPreEuDirective = $data->isPreEuDirective();

        return new self(
            $defectId,
            $parentCategoryId,
            $description,
            $defectBreadcrumb,
            $advisoryText,
            $inspectionManualReference,
            $isAdvisory,
            $isPrs,
            $isFailure,
            $deficiencyCategoryCode,
            $isPreEuDirective
        );
    }

    /**
     * @return int
     */
    public function getDefectId()
    {
        return $this->defectId;
    }

    /**
     * @return int
     */
    public function getParentCategoryId()
    {
        return $this->parentCategoryId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDefectBreadcrumb()
    {
        return $this->defectBreadcrumb;
    }

    /**
     * @param string $defectBreadcrumb
     */
    public function setDefectBreadcrumb($defectBreadcrumb)
    {
        $this->defectBreadcrumb = $defectBreadcrumb;
    }

    /**
     * @return string
     */
    public function getAdvisoryText()
    {
        return $this->advisoryText;
    }

    /**
     * @return string
     */
    public function getInspectionManualReference()
    {
        return $this->inspectionManualReference;
    }

    /**
     * @return bool
     */
    public function isAdvisory()
    {
        return $this->advisory;
    }

    /**
     * @return bool
     */
    public function isPrs()
    {
        return $this->prs;
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return $this->failure;
    }

    /**
     * @return string
     */
    public function getDeficiencyCategoryCode()
    {
        return $this->deficiencyCategoryCode;
    }

    /**
     * @param string $deficiencyCategoryCode
     */
    public function setDeficiencyCategoryCode($deficiencyCategoryCode)
    {
        $this->deficiencyCategoryCode = $deficiencyCategoryCode;
    }

    /**
     * @param string $inspectionManualReferenceUrl
     */
    public function setInspectionManualReferenceUrl($inspectionManualReferenceUrl)
    {
        $this->inspectionManualReferenceUrl = $inspectionManualReferenceUrl;
    }

    /**
     * @return string
     */
    public function getInspectionManualReferenceUrl()
    {
        return $this->inspectionManualReferenceUrl;
    }

    /**
     * @return bool
     */
    public function isPreEuDirective()
    {
        return $this->preEuDirective;
    }

    /**
     * @param bool $preEuDirective
     */
    public function setPreEuDirective($preEuDirective)
    {
        $this->preEuDirective = $preEuDirective;
    }

    /**
     * @return bool
     */
    public function getLabelForRfr()
    {
        switch ($this->deficiencyCategoryCode) {
            case RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE:
                return self::FAILURE_LABEL_TEXT;
            case RfrDeficiencyCategoryCode::DANGEROUS:
                return self::DANGEROUS_LABEL_TEXT;
            case RfrDeficiencyCategoryCode::MAJOR:
                return self::MAJOR_LABEL_TEXT;
            case RfrDeficiencyCategoryCode::MINOR:
                return self::MINOR_LABEL_TEXT;
        }
    }

    /**
     * @return bool
     */
    public function isMinorDefect()
    {
        return $this->getDeficiencyCategoryCode() === RfrDeficiencyCategoryCode::MINOR;
    }

    /**
     * @return bool
     */
    public function canDisplayMinorButton()
    {
        return $this->isMinorDefect();
    }

    /**
     * @return bool
     */
    public function canDisplayPrsButton()
    {
        return $this->isPrs() && !$this->isMinorDefect();
    }

    /**
     * @return bool
     */
    public function canDisplayFailureButton()
    {
        return ($this->isPrs() || $this->isFailure()) && !$this->isMinorDefect();
    }

    /**
     * @return bool
     */
    public function canDisplayAdvisoryButton()
    {
        return $this->isAdvisory();
    }
}
