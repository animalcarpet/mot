<?php

namespace DvsaMotApi\Dto\Builders;

use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;

class DefectDtoBuilder
{
    /**
     * @var DefectSentenceCaseConverter
     */
    private $defectSentenceCaseConverter;

    /**
     * DefectDtoBuilder constructor.
     *
     * @param DefectSentenceCaseConverter $defectSentenceCaseConverter
     */
    public function __construct(DefectSentenceCaseConverter $defectSentenceCaseConverter)
    {
        $this->defectSentenceCaseConverter = $defectSentenceCaseConverter;
    }

    /**
     * @param ReasonForRejection          $reasonForRejection
     * @param DefectSentenceCaseConverter $defectSentenceCaseConverter
     *
     * @return DefectDto
     */
    public function fromEntity(ReasonForRejection $reasonForRejection)
    {
        $formattedDefectDetails = $this->defectSentenceCaseConverter->getDefectDetailsForAddADefect($reasonForRejection);

        $defectDto = new DefectDto();
        $defectDto->setId($reasonForRejection->getRfrId());
        $defectDto->setParentCategoryId($reasonForRejection->getTestItemSelector()->getId());
        $defectDto->setDefectBreadcrumb($reasonForRejection->getTestItemSelectorName());
        $defectDto->setDescription($formattedDefectDetails['description']);
        $defectDto->setAdvisoryText($formattedDefectDetails['advisoryText']);
        $defectDto->setInspectionManualReference($reasonForRejection->getInspectionManualReference());
        $defectDto->setAdvisory($reasonForRejection->getIsAdvisory());
        $defectDto->setPrs($reasonForRejection->getIsPrsFail());
        $defectDto->setFailure(!$reasonForRejection->getIsAdvisory() && !$reasonForRejection->getIsPrsFail());

        return $defectDto;
    }
}
