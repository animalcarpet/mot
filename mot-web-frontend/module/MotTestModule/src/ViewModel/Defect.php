<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use DvsaCommon\Dto\MotTesting\DefectDto;

class Defect
{
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
        $isFailure
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
        $defectBreadcrumb = DefectSentenceCaseConverter::convert($data->getDefectBreadcrumb());
        $advisoryText = $data->getAdvisoryText();
        $inspectionManualReference = $data->getInspectionManualReference();
        $isAdvisory = $data->isAdvisory();
        $isPrs = $data->isPrs();
        $isFailure = $data->isFailure();

        return new self(
            $defectId,
            $parentCategoryId,
            $description,
            $defectBreadcrumb,
            $advisoryText,
            $inspectionManualReference,
            $isAdvisory,
            $isPrs,
            $isFailure
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
}
