<?php
/**
 * This is just an extra comment block to prevent the default auto-folding of the real comment below
 */
/**
 * A script to facilitate the automatic code generation of a the "Type1" enum classes -
 * on which business or presentation logic depends - containing the static /lookup data from the database.
 *
 * Usage:
 * On the vagrant VM, cd to workspace/mot-common-web-module and run
 *      php generate_enums.php
 * Check in the resulting changes in 'mot-common-web-module/src/DvsaCommon/Enum'.
 */

include 'enum-generation/EnumGenerationBlueprint.php';
include 'enum-generation/EnumGenerationBlueprintWithIntValue.php';
include 'enum-generation/EnumGenerationHelper.php';

const ENUM_DIR_PATH = 'src/DvsaCommon/Enum';

/**
 * @var EnumGeneration\EnumGenerationBlueprint[] $inputArray
 *
 * Specify the name of the Enum to be created here and the database table and columns used to create it.
 */
$inputArray = [
    enum('AuthorisationForAuthorisedExaminerStatusCode', 'auth_for_ae_status', 'name', 'code'),
    enum('AuthorisationForTestingMotAtSiteStatusCode', 'auth_for_testing_mot_at_site_status', 'name', 'code'),
    enum('AuthorisationForTestingMotStatusCode', 'auth_for_testing_mot_status', 'name', 'code'),
    enum('BrakeTestTypeCode', 'brake_test_type', 'name', 'code'),
    enum('BusinessRoleStatusCode', 'business_role_status', 'name', 'code'),
    enum('CertificateTypeCode', 'certificate_type', 'name', 'code'),
    enum('ColourCode', 'colour_lookup', 'name', 'code'),
    enum('CountryCode', 'country_lookup', 'name', 'code'),
    enum('CountryOfRegistrationCode', 'country_of_registration_lookup', 'name', 'code'),
    enum('DirectDebitStatusCode', 'direct_debit_status', 'name', 'code'),
    enum('EmergencyReasonCode', 'emergency_reason_lookup', 'name', 'code'),
    enum('EquipmentModelStatusCode', 'equipment_model_status', 'name', 'code'),
    enum('EventTypeCode', 'event_type_lookup', 'name', 'code'),
    enum('FuelTypeCode', 'fuel_type', 'name', 'code'),
    enum('LanguageTypeCode', 'language_type', 'name', 'code'),
    enum('LicenceCountryCode', 'licence_country_lookup', 'name', 'code'),
    enum('LicenceTypeCode', 'licence_type', 'name', 'code'),
    enum('MessageTypeCode', 'message_type', 'name', 'code'),
    enum('MotTestTypeCode', 'mot_test_type', 'description', 'code'),
    enum('OrganisationBusinessRoleCode', 'organisation_business_role', 'name', 'name'), //TODO: this will use 'code' instead of 'name', once VM-8254 point 4 takes place
    enum('RoleCode', 'role', 'name', 'code'), // Unfortunately the correct name for this class has been registered by the previous line (OrganisationBusinessRoleCode) and most importantly codes on the organisation_business_role and role tables are not matching!!
    enum('OrganisationContactTypeCode', 'organisation_contact_type', 'name', 'code'),
    enum('PersonAuthType', 'person_auth_type_lookup', 'name', 'code'),
    enum('PhoneContactTypeCode', 'phone_contact_type', 'name', 'code'),
    enum('PersonContactTypeCode', 'person_contact_type', 'name', 'code'),
    enum('SiteBusinessRoleCode', 'site_business_role', 'name', 'code'),
    enum('SiteContactTypeCode', 'site_contact_type', 'name', 'code'),
    enum('SiteTypeCode', 'site_type', 'name', 'code'),
    enum('SiteTypeName', 'site_type', 'name', 'name'),
    enum('TransitionStatusCode', 'transition_status', 'name', 'code'),
    enum('VehicleClassCode', 'vehicle_class', 'name', 'code', 'CLASS_'),
    enum('VehicleClassGroupCode', 'vehicle_class_group', 'name', 'code'),
    enum('WeightSourceCode', 'weight_source_lookup', 'name', 'code'),
    enum('MotTestStatusId', 'mot_test_status', 'name', 'id'),
    enum('OrganisationSiteStatusCode', 'organisation_site_status', 'name', 'code'),
    enum('MotTestStatusCode', 'mot_test_status', 'name', 'code'),

    // Site Statuses
    enum('SiteStatusCode', 'site_status_lookup', 'name', 'code'),

    // Enums which has values that are database names, ideally usages of these would be replaced with the 'code' column.
    enum('EventTypeName', 'event_type_lookup', 'name', 'name'),
    enum('EventOutcomeCode', 'event_outcome_lookup', 'code', 'code'),
    enum('EventOutcomeName', 'event_outcome_lookup', 'code', 'name'),
    enum('EventCategoryCode', 'event_category_lookup', 'name', 'code'),
    enum('EventCategoryName', 'event_category_lookup', 'name', 'name'),
    enum('CompanyTypeName', 'company_type', 'name', 'name'),
    enum('CompanyTypeCode', 'company_type', 'name', 'code'),
    enum('MotTestStatusName', 'mot_test_status', 'name', 'name'),
    enum('BusinessRoleName', 'person_system_role', 'name', 'name'),
    enum('ReasonForRejectionTypeName', 'reason_for_rejection_type', 'name', 'name'),
    enum('OrganisationSiteStatusName', 'organisation_site_status', 'name', 'name'),

    // Enums which has values that are database IDs, ideally usages of these would be replaced with the 'code' column.
    enum('ColourId', 'colour_lookup', 'name', 'id'),
    enum('CountryOfRegistrationId', 'country_of_registration_lookup', 'name', 'id'),
    enum('EnfDecisionId', 'enforcement_decision_lookup', 'decision', 'id'),
    enum('EnfDecisionOutcomeId', 'enforcement_decision_outcome_lookup', 'outcome', 'id'),
    enum('EnfDecisionReinspectionOutcomeId', 'enforcement_decision_reinspection_outcome_lookup', 'decision', 'id'),
    enum('EnfRetestModeId', 'enforcement_full_partial_retest_lookup', 'description', 'id'),
    enum('EnfSiteVisitOutcomeId', 'enforcement_visit_outcome_lookup', 'description', 'id'),
    enum('FuelTypeId', 'fuel_type', 'name', 'id'),
    enum('OrganisationBusinessRoleId', 'organisation_business_role', 'name', 'id'),
    enum('OrganisationBusinessRoleName', 'organisation_business_role', 'name', 'description'),
    enum('ReasonForCancelId', 'mot_test_reason_for_cancel_lookup', 'code', 'id'),
    enum('SpecialNoticeAudienceTypeId', 'special_notice_audience_type', 'name', 'id'),
    enum('VehicleClassId', 'vehicle_class', 'name', 'id', 'CLASS_'),
];

$constantGeneratorHelper = new EnumGeneration\EnumGenerationHelper();
$constantGeneratorHelper->setDirectoryPath(ENUM_DIR_PATH);
$constantGeneratorHelper->createDirectoryIfNotExisting();
$constantGeneratorHelper->removeAllPreviouslyGeneratedEnums();
$constantGeneratorHelper->setupDatabaseConnection('mot', 'mysql', 'motdbuser', 'password');

$constantGeneratorHelper->generateEnumClasses($inputArray);

function enum($enumName, $tableName, $columnNameForEnumKey, $columnNameForEnumValue, $prefix = '')
{
    if ($columnNameForEnumValue === 'id') {
        return (new EnumGeneration\EnumGenerationBlueprintWithIntValue(
            $enumName, $tableName, $columnNameForEnumKey, $columnNameForEnumValue, $prefix
        ));
    }
    return (new EnumGeneration\EnumGenerationBlueprint(
        $enumName, $tableName, $columnNameForEnumKey, $columnNameForEnumValue, $prefix
    ));
}
