<?php

namespace DvsaMotApi\Service\Validator\RetestEligibility;

/**
 *  Provides functionality that translates error codes of retest eligibility check into human readable from.
 *
 * Class RetestEligibilityErrorCodeTranslator
 */
class RetestEligibilityErrorCodeTranslator
{
    /**
     * Translates code (see RetestEligibilityCheckCode) into human-readable form.
     *
     * @param $code
     *      one of the codes from RetestEligibilityCheckCode
     *
     * @return string
     *                human readable description of an error
     */
    public static function toText($code)
    {
        $text = '';
        switch ($code) {
            case RetestEligibilityCheckCode::RETEST_REJECTED_ALREADY_REGISTERED:
                $text = 'Re-test already registered for original test';
                break;
            case RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_CANCELLED:
                $text = 'Original test was cancelled';
                break;
            case RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_NEVER_PERFORMED:
                $text = 'Original test never performed';
                break;
            case RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS:
                $text = 'Original test was performed at a different VTS';
                break;
            case RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_MORE_THAN_10_WORKING_DAYS:
                $text = 'Original test was performed more than 10 working days ago';
                break;
            case RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED:
                $text = 'Original test was not failed';
                break;
        }

        return $text;
    }
}
