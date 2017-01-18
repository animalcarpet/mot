<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommon\Constants;

/**
 * Class OdometerUnit
 *
 * Enums defined on mot_test_current.odometer_unit and certificate_replacement_draft.odometer_unit
 */
class OdometerUnit extends BaseEnumeration
{
    const MILES = 'mi';
    const KILOMETERS = 'km';
}
