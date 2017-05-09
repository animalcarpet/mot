<?php

namespace DvsaMotApi\Service\Validator\RetestEligibility;

/**
 *  A list of possible codes returned by RetestEligibilityService.
 */
class RetestEligibilityCheckCode
{
    // positives
    const RETEST_GRANTED = 0;

    // errors
    const RETEST_REJECTED_ORIGINAL_PERFORMED_MORE_THAN_10_WORKING_DAYS = 1;
    const RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS = 2;
    const RETEST_REJECTED_ORIGINAL_CANCELLED = 3;
    const RETEST_REJECTED_ALREADY_REGISTERED = 4;
    const RETEST_REJECTED_ORIGINAL_NEVER_PERFORMED = 5;
    const RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED = 6;
}
