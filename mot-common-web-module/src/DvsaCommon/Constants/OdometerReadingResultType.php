<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommon\Constants;

/**
 * Class OdometerReadingResultType
 *
 * Enums defined on mot_test_current.odometer_result_type and certificate_replacement_draft.odometer_result_type
 */
class OdometerReadingResultType extends BaseEnumeration
{
    const OK = 'OK';
    const NO_ODOMETER = 'NO_METER';
    const NOT_READABLE = 'NOT_READ';
}
