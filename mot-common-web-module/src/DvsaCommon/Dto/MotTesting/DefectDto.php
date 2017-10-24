<?php
/**
 * This file is part of the DVSA MOT Common project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommon\Dto\MotTesting;

use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaEntities\Entity\RfrDeficiencyCategory;
use JsonSerializable;

class DefectDto implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $parentCategoryId;

    /**
     * @var string
     */
    private $description;

    /**
     * The breadcrumb string with chevrons.
     *
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
     * @var String
     */
    private $deficiencyCategoryCode;

    /**
     * @var bool
     */
    private $preEuDirective;

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'parentCategoryId' => $this->getParentCategoryId(),
            'description' => $this->getDescription(),
            'defectBreadcrumb' => $this->getDefectBreadcrumb(),
            'advisoryText' => $this->getAdvisoryText(),
            'inspectionManualReference' => $this->getInspectionManualReference(),
            'advisory' => $this->isAdvisory(),
            'prs' => $this->isPrs(),
            'failure' => $this->isFailure(),
            'deficiencyCategoryCode' => $this->getDeficiencyCategoryCode(),
            'preEuDirective' => $this->isPreEuDirective(),
        ];
    }

    /**
     * @param int $id
     *
     * @return DefectDto
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $parentCategoryId
     */
    public function setParentCategoryId($parentCategoryId)
    {
        $this->parentCategoryId = $parentCategoryId;
    }

    /**
     * @return int
     */
    public function getParentCategoryId()
    {
        return $this->parentCategoryId;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @param string $advisoryText
     */
    public function setAdvisoryText($advisoryText)
    {
        $this->advisoryText = $advisoryText;
    }

    /**
     * @return string
     */
    public function getAdvisoryText()
    {
        return $this->advisoryText;
    }

    /**
     * @param string $inspectionManualReference
     */
    public function setInspectionManualReference($inspectionManualReference)
    {
        $this->inspectionManualReference = $inspectionManualReference;
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
     * @param bool $advisory
     */
    public function setAdvisory($advisory)
    {
        $this->advisory = $advisory;
    }

    /**
     * @return bool
     */
    public function isPrs()
    {
        return $this->prs;
    }

    /**
     * @param bool $prs
     */
    public function setPrs($prs)
    {
        $this->prs = $prs;
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return $this->failure;
    }

    /**
     * @param bool $failure
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
    }

    /**
     * @return String
     */
    public function getDeficiencyCategoryCode()
    {
        return $this->deficiencyCategoryCode;
    }

    /**
     * @param String $deficiencyCategoryCode
     */
    public function setDeficiencyCategoryCode($deficiencyCategoryCode)
    {
        $this->deficiencyCategoryCode = $deficiencyCategoryCode;
    }

    /**
     * @return boolean
     */
    public function isPreEuDirective()
    {
        return $this->preEuDirective;
    }

    /**
     * @param boolean $preEuDirective
     */
    public function setPreEuDirective($preEuDirective)
    {
        $this->preEuDirective = $preEuDirective;
    }
}
