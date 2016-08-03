<?php


namespace DvsaCommon\Constants;

/**
 * Class EventDescription
 *
 * @package DvsaCommon\Constants
 */
class EventDescription extends BaseEnumeration
{
    const USER_CLAIMS_ACCOUNT = 'Account claimed by user %s';
    const USER_RECLAIMS_ACCOUNT = 'Account reclaimed by user %s';
    const USER_ACCOUNT_RESET = 'Account for user %s reset by %s';
    const ROLE_ASSOCIATION_CHANGE = '%s role associated with %s (%s)';
    const ROLE_ASSOCIATION_REMOVE = '%s role removed from %s (%s)';
    const ROLE_ASSOCIATION_REMOVE_SITE_ORG = '%s role for %s removed from %s (%s)';
    const ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG = '%s role for %s - %s removed from %s (%s)';
    const DVSA_ADMINISTRATOR_CREATE_AE = 'Authorised examiner %s %s created by user %s';
    const DVSA_ADMINISTRATOR_CREATE_SITE = 'Site %s %s created by user %s';
    const DVSA_ADMINISTRATOR_UPDATE_SITE = '%s has been updated from %s to %s for Site %s %s by user %s';
    const DVSA_ADMINISTRATOR_AMEND_AREA_OFFICE = 'Area Office has been updated from %s to %s for Authorised Examiner %s %s by user %s';
    const DVSA_ADMINISTRATOR_UPDATE_AE_STATUS = 'Status has been updated from %s to %s for Authorised Examiner %s %s by user %s';
    const UPDATE_AE_PROPERTY = '%s has been updated from "%s" to "%s" for Authorised Examiner %s %s by user %s';
    const TESTER_QUALIFICATION_STATUS_CHANGE =
        'Qualified to test Group %s vehicles following a demonstration test. Recorded by %s';

    const DVSA_ROLE_ASSOCIATION_ASSIGN = 'Assigned %s role. Recorded by %s';
    const DVSA_ROLE_ASSOCIATION_REMOVE = 'Removed %s role. Recorded by %s';
    const ROLE_NOMINATION_ACCEPT = '%s role for %s - %s added to %s %s';

    const TESTER_QUALIFICATION_STATUS_CHANGE_NEW = 'Tester qualification status for group %s has been changed to %s, by %s';
    const TESTER_QUALIFICATION_STATUS_CHANGE_UPDATE = 'Tester qualification status for group %s changed from %s to %s, by %s';

    const MOT_TESTING_QUALIFICATION_CERTIFICATE_RECORD = 'Tester qualification certificate details for group %s recorded by %s. Certificate number %s and Certificate date %s.';
    const MOT_TESTING_QUALIFICATION_CERTIFICATE_EDIT = 'Tester qualification certificate details for group %s edited by %s. Details changed to Certificate number %s and Certificate date %s';
    const MOT_TESTING_QUALIFICATION_CERTIFICATE_REMOVE = 'Tester qualification certificate removed for group %s by %s. Certificate number %s and Certificate date %s';

    const MOT_TESTING_ANNUAL_CERTIFICATE_RECORD = 'Tester annual exam details for group %s recorded by %s. Certificate number %s and Certificate date %s';
    const MOT_TESTING_ANNUAL_CERTIFICATE_EDIT = 'Tester annual exam details for group %s changed by %s. Details changed from: Certificate number %s to %s, Exam date %s to %s, Score achieved %s%% to %s%%.';
    const MOT_TESTING_ANNUAL_CERTIFICATE_REMOVE = 'Tester annual exam removed for group %s by %s. Certificate number %s, Exam date %s and score achieved %s%%';

    const DVSA_ADMINISTRATOR_LINK_A_SITE_TO_AN_AE = 'Site %s %s has been linked to AE %s %s by %s';
    const AE_UNLINK_SITE = 'Site %s %s has been unlinked from AE %s %s by %s';
    const SITE_ASSESSMENT_RISK_SCORE = "Site assessment risk score %.2f for %s %s has been carried out by %s";

    const AEP_ADDED_TO_AE = 'AEP %s %s has been added to Authorised Examiner %s %s by %s %s';
    const AEP_REMOVED_TO_AE = 'AEP %s %s has been removed from Authorised Examiner %s %s by %s %s';
}
