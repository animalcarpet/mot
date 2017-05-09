<?php

namespace IntegrationApi\OpenInterface\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;

/**
 * Class OpenInterfaceMotTestRequestValidator.
 */
class OpenInterfaceMotTestRequestValidator extends AbstractValidator
{
    /**
     * Date valid when in a format YYYYMMDD.
     *
     * @param $date
     */
    public function validateDate($date)
    {
        if (8 !== mb_strlen($date)) {
            ErrorSchema::throwError('Invalid date length');
        }

        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        try {
            DateUtils::validateDateByParts($day, $month, $year);
        } catch (DateException $e) {
            ErrorSchema::throwError('Invalid date');
        }
    }
}
