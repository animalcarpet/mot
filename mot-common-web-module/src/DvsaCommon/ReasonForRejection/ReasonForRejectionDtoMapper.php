<?php
namespace DvsaCommon\ReasonForRejection;

use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;

class ReasonForRejectionDtoMapper
{
    public static function mapSingle(array $reasonForRejection): ReasonForRejectionDto
    {
        $url = InspectionManualReferenceUrlBuilder::build(ArrayUtils::get($reasonForRejection, "inspectionManualReference"), ArrayUtils::get($reasonForRejection, "vehicleClassCode"));
        $isPreEuDirective = (ArrayUtils::get($reasonForRejection, "deficiencyCategoryCode") === RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE);

        $dto = new ReasonForRejectionDto();
        $dto
            ->setRfrId((int) ArrayUtils::get($reasonForRejection, "rfrId"))
            ->setTestItemSelectorId((int)ArrayUtils::get($reasonForRejection,"testItemSelectorId"))
            ->setDescription(ArrayUtils::tryGet($reasonForRejection, "description", ""))
            ->setTestItemSelectorName(ArrayUtils::tryGet($reasonForRejection, "testItemSelectorName", ""))
            ->setAdvisoryText(ArrayUtils::get($reasonForRejection, "advisoryText"))
            ->setInspectionManualReference(ArrayUtils::get($reasonForRejection, "inspectionManualReference"))
            ->setInspectionManualReferenceUrl($url)
            ->setIsAdvisory(ArrayUtils::get($reasonForRejection, "isAdvisory"))
            ->setIsPrsFail(ArrayUtils::get($reasonForRejection, "isPrsFail"))
            ->setDeficiencyCategoryCode(ArrayUtils::get($reasonForRejection, "deficiencyCategoryCode"))
            ->setIsPreEuDirective($isPreEuDirective)
        ;

        return $dto;
    }

    /**
     * @param array $reasonsForRejection
     * @return ReasonForRejectionDto[]
     */
    public static function mapMany(array $reasonsForRejection): array
    {
        $dtoArray = [];
        foreach ($reasonsForRejection as $rfr) {
            $dtoArray[] = self::mapSingle($rfr);
        }

        return $dtoArray;
    }
}
