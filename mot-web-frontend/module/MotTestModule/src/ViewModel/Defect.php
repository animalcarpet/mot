<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

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
    private $defectBreadcrumb;

    /**
     * @var string
     */
    private $description;

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
     * @param $defectId
     * @param $parentCategoryId
     * @param $defectBreadcrumb
     * @param $description
     * @param $advisoryText
     * @param $inspectionManualReference
     * @param $isAdvisory
     * @param $isPrs
     * @param $isFailure
     */
    public function __construct(
        $defectId,
        $parentCategoryId,
        $defectBreadcrumb,
        $description,
        $advisoryText,
        $inspectionManualReference,
        $isAdvisory,
        $isPrs,
        $isFailure
    ) {
        $this->defectId = $defectId;
        $this->parentCategoryId = $parentCategoryId;
        $this->defectBreadcrumb = $defectBreadcrumb;
        $this->description = $description;
        $this->advisoryText = $advisoryText;
        $this->inspectionManualReference = $inspectionManualReference;
        $this->advisory = $isAdvisory;
        $this->prs = $isPrs;
        $this->failure = $isFailure;
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
    public function getDefectBreadcrumb()
    {
        return $this->defectBreadcrumb;
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