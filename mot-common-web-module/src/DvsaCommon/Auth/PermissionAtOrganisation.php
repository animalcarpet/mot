<?php
namespace DvsaCommon\Auth;

/**
 * List of all organisation level permissions. Global or Site permissions should go to their respective classes.
 */
class PermissionAtOrganisation
{
    const VIEW_AE_PERSONNEL = 'VIEW-AE-PERSONNEL';
    const MANAGE_AE_PERSONNEL = 'MANAGE-AE-PERSONNEL';
    const AE_SLOTS_USAGE_READ = 'AE-SLOTS-USAGE-READ';
    const AE_SLOTS_BALANCE_READ = 'AE-SLOTS-BALANCE-READ';
    const SLOTS_TRANSACTION_HISTORY_READ = 'SLOTS-TRANSACTION-HISTORY-READ';
    const VEHICLE_TESTING_STATION_LIST_AT_AE = 'VEHICLE-TESTING-STATION-LIST-AT-AE';
    const SLOTS_PURCHASE = 'SLOTS-PURCHASE';
    const SLOTS_PAYMENT_DIRECT_DEBIT = 'SLOTS-PAYMENT-DIRECT-DEBIT';
    const SLOTS_TRANSACTION_READ_FULL = 'SLOTS-TRANSACTION-READ-FULL';
    const SLOTS_ADJUSTMENT            = 'SLOTS-ADJUSTMENT';
    const SLOTS_INSTANT_SETTLEMENT = 'SLOTS-PURCHASE-INSTANT-SETTLEMENT';
    const AUTHORISED_EXAMINER_READ = 'AUTHORISED-EXAMINER-READ';
    const AUTHORISED_EXAMINER_UPDATE = 'AUTHORISED-EXAMINER-UPDATE';
    const AE_TEST_LOG = 'AE-TEST-LOG';
    const MOT_TEST_LIST = 'MOT-TEST-LIST';
    const LIST_AEP_AT_AUTHORISED_EXAMINER = 'LIST-AEP-AT-AUTHORISED-EXAMINER';
    const LIST_AE_POSITIONS = 'LIST-AE-POSITIONS';
    const NOMINATE_ROLE_AT_AE = 'NOMINATE-ROLE-AT-AE';
    const REMOVE_POSITION_FROM_AE = 'REMOVE-POSITION-FROM-AE';
    const REMOVE_AEDM_FROM_AE = 'REMOVE-AEDM-FROM-AE';
    const AE_EMPLOYEE_PROFILE_READ = 'AE-EMPLOYEE-PROFILE-READ';
    const AUTHORISED_EXAMINER_PRINCIPAL_CREATE = 'AUTHORISED-EXAMINER-PRINCIPAL-CREATE';
    const AUTHORISED_EXAMINER_PRINCIPAL_REMOVE = 'AUTHORISED-EXAMINER-PRINCIPAL-REMOVE';

    public static function all()
    {
        return [
            self::VIEW_AE_PERSONNEL,
            self::MANAGE_AE_PERSONNEL,
            self::AE_SLOTS_USAGE_READ,
            self::AE_SLOTS_BALANCE_READ,
            self::SLOTS_PURCHASE,
            self::SLOTS_PAYMENT_DIRECT_DEBIT,
            self::SLOTS_TRANSACTION_READ_FULL,
            self::SLOTS_ADJUSTMENT,
            self::SLOTS_INSTANT_SETTLEMENT,
            self::AUTHORISED_EXAMINER_READ,
            self::AUTHORISED_EXAMINER_UPDATE,
            self::AE_TEST_LOG,
            self::MOT_TEST_LIST,
            self::VEHICLE_TESTING_STATION_LIST_AT_AE,
            self::LIST_AEP_AT_AUTHORISED_EXAMINER,
            self::LIST_AE_POSITIONS,
            self::NOMINATE_ROLE_AT_AE,
            self::REMOVE_POSITION_FROM_AE,
            self::REMOVE_AEDM_FROM_AE,
            self::AE_EMPLOYEE_PROFILE_READ,
            self::SLOTS_TRANSACTION_HISTORY_READ,
            self::AUTHORISED_EXAMINER_PRINCIPAL_CREATE,
            self::AUTHORISED_EXAMINER_PRINCIPAL_REMOVE,
        ];
    }
}
