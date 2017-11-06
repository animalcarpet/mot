<?php
namespace DvsaCommon\Dto\ReasonForRejection;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ReasonForRejectionDto implements ReflectiveDtoInterface
{
    /** @var int */
    private $rfrId;
    /** @var int */
    private $testItemSelectorId;
    /** @var string */
    private $description;
    /** @var string */
    private $testItemSelectorName;
    /** @var string */
    private $advisoryText;
    /** @var string */
    private $inspectionManualReference;
    /** @var string */
    private $setInspectionManualReferenceUrl;
    /** @var bool */
    private $isAdvisory;
    /** @var bool */
    private $isPrsFail;
    /** @var string */
    private $deficiencyCategoryCode;
    /** @var bool */
    private $isPreEuDirective;

    public function getRfrId(): int
    {
        return $this->rfrId;
    }

    /**
     * @param int $rfrId
     * @return ReasonForRejectionDto
     */
    public function setRfrId(int $rfrId): self
    {
        $this->rfrId = $rfrId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTestItemSelectorId(): int
    {
        return $this->testItemSelectorId;
    }

    /**
     * @param int $testItemSelectorId
     * @return ReasonForRejectionDto
     */
    public function setTestItemSelectorId(int $testItemSelectorId): self
    {
        $this->testItemSelectorId = $testItemSelectorId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ReasonForRejectionDto
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestItemSelectorName(): string
    {
        return $this->testItemSelectorName;
    }

    /**
     * @param string $testItemSelectorName
     * @return ReasonForRejectionDto
     */
    public function setTestItemSelectorName(string $testItemSelectorName): self
    {
        $this->testItemSelectorName = $testItemSelectorName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdvisoryText(): string
    {
        return $this->advisoryText;
    }

    public function setAdvisoryText(string $advisoryText): self
    {
        $this->advisoryText = $advisoryText;
        return $this;
    }

    /**
     * @return string
     */
    public function getInspectionManualReference(): string
    {
        return $this->inspectionManualReference;
    }

    /**
     * @param string $inspectionManualReference
     * @return ReasonForRejectionDto
     */
    public function setInspectionManualReference(string $inspectionManualReference): self
    {
        $this->inspectionManualReference = $inspectionManualReference;
        return $this;
    }

    /**
     * @return string
     */
    public function getInspectionManualReferenceUrl(): string
    {
        return $this->setInspectionManualReferenceUrl;
    }

    /**
     * @param string $url
     * @return ReasonForRejectionDto
     */
    public function setInspectionManualReferenceUrl(string $url): self
    {
        $this->setInspectionManualReferenceUrl = $url;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdvisory(): bool
    {
        return $this->isAdvisory;
    }

    /**
     * @param bool $isAdvisory
     * @return ReasonForRejectionDto
     */
    public function setIsAdvisory(bool $isAdvisory): self
    {
        $this->isAdvisory = $isAdvisory;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPrsFail(): bool
    {
        return $this->isPrsFail;
    }

    /**
     * @param bool $isPrsFail
     * @return ReasonForRejectionDto
     */
    public function setIsPrsFail(bool $isPrsFail): self
    {
        $this->isPrsFail = $isPrsFail;
        return $this;
    }

    public function getDeficiencyCategoryCode() :string
    {
        return $this->deficiencyCategoryCode;
    }

    public function setDeficiencyCategoryCode(string $deficiencyCategoryCode) :self
    {
        $this->deficiencyCategoryCode = $deficiencyCategoryCode;
        return $this;
    }

    public function getIsPreEuDirective(): bool
    {
        return $this->isPreEuDirective;
    }

    public function setIsPreEuDirective(bool $isPreEuDirective): self
    {
        $this->isPreEuDirective = $isPreEuDirective;
        return $this;
    }
}
