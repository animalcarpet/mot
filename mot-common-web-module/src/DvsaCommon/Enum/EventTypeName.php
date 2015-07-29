<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'event_type_lookup' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class EventTypeName
{
    const MOT_MANAGEMENT_TRAINING = 'MOT Management Training';
    const APPEAL_AGAINST_DISCIPLINARY_ACTION = 'Appeal against disciplinary action';
    const CONVICTIONS_MOT_MOTOR_TRADE_CRIMINAL = 'Convictions: MOT, motor trade, criminal';
    const DESK_BASED_ASSESSMENT = 'Desk Based Assessment';
    const DBA_REFERRAL_TO_AREA_OFFICE = 'DBA referral to Area Office';
    const VT6_8_IN_ROLLOUT = 'VT6/8 in rollout';
    const DISCIPLINARY_ACTION = 'Disciplinary Action';
    const DIRECTED_SITE_VISIT = 'Directed Site Visit';
    const MYSTERY_SHOPPER = 'Mystery Shopper';
    const LEVEL_1_ACTION = 'Level 1 action';
    const MEMO = 'Memo';
    const APPEAL_DISALLOWED = 'Appeal disallowed';
    const APPEALS_BR_APPEAL_INC_FORMAL_WARNING = 'Appeals Br: appeal inc formal warning';
    const APPEALS_BR_APPEAL_REJECT_OUT_OF_TIME = 'Appeals Br: appeal reject out of time';
    const APPEALS_BR_APPEAL_WITHDRAWN = 'Appeals Br: appeal withdrawn';
    const APPEALS_BR_APPEAL_UPHELD = 'Appeals Br: appeal upheld';
    const FAILURE_TO_NOTIFY_VTS_CLOSURE_CHANGE_OWNER = 'Failure to notify VTS closure/change owner';
    const MIGRATED_NTT_CLASSES_UNSPECIFIED = 'Migrated NTT: classes unspecified';
    const NTT_INITIAL_CLASSES_3457 = 'NTT Initial: classes 3,4,5,7';
    const NTT_DIRECTED_RETRAINING_CLASSES_3457 = 'NTT Directed Retraining: classes 3,4,5,7';
    const NTT_MOTORCYCLE = 'NTT Motorcycle';
    const NTT_REFRESHER_CLASSES_3457 = 'NTT Refresher: classes 3,4,5,7';
    const MIGRATED_NTT_REFRESHER_CLASSES_3457 = 'Migrated NTT Refresher: classes 3,4,5,7';
    const LOSS_OF_REPUTE_CONVICTIONS = 'Loss of repute/convictions';
    const REVIEW_OF_FORMAL_WARNING = 'Review of Formal Warning';
    const SITE_ASSESSMENT = 'Site Assessment';
    const SPECIAL_INVESTIGATION = 'Special Investigation';
    const TRANSFER_SITE_ASSESSMENT_TO_NEW_AE_VTS_LINK = 'Transfer Site Assessment to new AE-VTS link';
    const APPEAL_AGAINST_VT30_ISSUE = 'Appeal against VT30 issue';
    const APPEAL_AGAINST_VT20_ISSUE = 'Appeal against VT20 issue';
    const SCHEDULED_VTS_VISIT = 'Scheduled VTS visit';
    const TARGETTED_VTS_VISIT = 'Targetted VTS visit';
    const TARGETED_RE_INSPECTION = 'Targeted Re-inspection';
    const MOT_COMPLIANCE_SURVEY = 'MOT Compliance Survey';
    const DEMONSTRATION_TEST = 'Demonstration test';
    const VT7 = 'VT7';
    const USER_CLAIMS_ACCOUNT = 'User Claims Account';
    const ROLE_ASSOCIATION_CHANGE = 'Role Association Change';
    const USER_RECLAIMS_ACCOUNT = 'User Reclaims Account';
    const USER_ACCOUNT_RESET = 'User Account Reset';
    const GROUP_A_TESTER_QUALIFICATION = 'Group A Tester Qualification';
    const GROUP_B_TESTER_QUALIFICATION = 'Group B Tester Qualification';
    const DVSA_ADMINISTRATOR_CREATE_AE = 'DVSA Administrator Create AE';
    const UPDATE_AE = 'Update AE';

    /**
     * @return array of values for the type EventTypeName
     */
    public static function getAll()
    {
        return [
            self::MOT_MANAGEMENT_TRAINING,
            self::APPEAL_AGAINST_DISCIPLINARY_ACTION,
            self::CONVICTIONS_MOT_MOTOR_TRADE_CRIMINAL,
            self::DESK_BASED_ASSESSMENT,
            self::DBA_REFERRAL_TO_AREA_OFFICE,
            self::VT6_8_IN_ROLLOUT,
            self::DISCIPLINARY_ACTION,
            self::DIRECTED_SITE_VISIT,
            self::MYSTERY_SHOPPER,
            self::LEVEL_1_ACTION,
            self::MEMO,
            self::APPEAL_DISALLOWED,
            self::APPEALS_BR_APPEAL_INC_FORMAL_WARNING,
            self::APPEALS_BR_APPEAL_REJECT_OUT_OF_TIME,
            self::APPEALS_BR_APPEAL_WITHDRAWN,
            self::APPEALS_BR_APPEAL_UPHELD,
            self::FAILURE_TO_NOTIFY_VTS_CLOSURE_CHANGE_OWNER,
            self::MIGRATED_NTT_CLASSES_UNSPECIFIED,
            self::NTT_INITIAL_CLASSES_3457,
            self::NTT_DIRECTED_RETRAINING_CLASSES_3457,
            self::NTT_MOTORCYCLE,
            self::NTT_REFRESHER_CLASSES_3457,
            self::MIGRATED_NTT_REFRESHER_CLASSES_3457,
            self::LOSS_OF_REPUTE_CONVICTIONS,
            self::REVIEW_OF_FORMAL_WARNING,
            self::SITE_ASSESSMENT,
            self::SPECIAL_INVESTIGATION,
            self::TRANSFER_SITE_ASSESSMENT_TO_NEW_AE_VTS_LINK,
            self::APPEAL_AGAINST_VT30_ISSUE,
            self::APPEAL_AGAINST_VT20_ISSUE,
            self::SCHEDULED_VTS_VISIT,
            self::TARGETTED_VTS_VISIT,
            self::TARGETED_RE_INSPECTION,
            self::MOT_COMPLIANCE_SURVEY,
            self::DEMONSTRATION_TEST,
            self::VT7,
            self::USER_CLAIMS_ACCOUNT,
            self::ROLE_ASSOCIATION_CHANGE,
            self::USER_RECLAIMS_ACCOUNT,
            self::USER_ACCOUNT_RESET,
            self::GROUP_A_TESTER_QUALIFICATION,
            self::GROUP_B_TESTER_QUALIFICATION,
            self::DVSA_ADMINISTRATOR_CREATE_AE,
            self::UPDATE_AE,
        ];
    }

    /**
     * @param mixed $key a candidate EventTypeName value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
