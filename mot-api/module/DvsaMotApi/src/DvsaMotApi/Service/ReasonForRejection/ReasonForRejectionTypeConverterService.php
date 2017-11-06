<?php
namespace DvsaMotApi\Service\ReasonForRejection;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;

class ReasonForRejectionTypeConverterService
{
    private $defectSentenceCaseConverter;

    public function __construct()
    {
        $this->defectSentenceCaseConverter = new DefectSentenceCaseConverter();
    }

    public function convert(array $reasonForRejection)
    {
        $reasonForRejection['isAdvisory'] = (bool) ArrayUtils::get($reasonForRejection, "isAdvisory");
        $reasonForRejection['isPrsFail'] = (bool) ArrayUtils::get($reasonForRejection,'isPrsFail');
        $reasonForRejection['vehicleClasses'] = explode(',', ArrayUtils::get($reasonForRejection,'vehicleClasses'));

        $defectDetails = $this->defectSentenceCaseConverter->getRawDefectDetailsForSearch(
            ArrayUtils::get($reasonForRejection,'description'), ArrayUtils::get($reasonForRejection,'advisoryText'), ArrayUtils::get($reasonForRejection,'categoryDescription')
        );

        $reasonForRejection = array_merge($reasonForRejection, $defectDetails);

        return $reasonForRejection;
    }
}
