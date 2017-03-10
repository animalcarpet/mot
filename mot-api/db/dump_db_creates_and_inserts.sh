#!/bin/bash

#
# Descr: Dumps MySQL table data into a schema create file and separate SQL insert files for the mot2 database.
#        Used to make the scripts used by create_db_with_test_data.sh
# Usage: dump_db_creates_and_inserts.sh [<MyUSER>] [<MyPASS>] [<MyHOST>] [<TO_ZIP>]
#
# Ref: http://stackoverflow.com/a/17016410
#

DB="mot2"

MyUSER=${1-"motdbuser"}
MyPASS=${2-"password"}
MyHOST=${3-"mysql"}
TO_ZIP=${4-false}

STATIC_DATA_DIR=~/MOTDEV/mot/mot-api/db/dev/populate/static-data
TEST_DATA_DIR=~/MOTDEV/mot/mot-api/db/dev/populate/test-data
SCHEMA_FILE=~/MOTDEV/mot/mot-api/db/dev/schema/create_dev_db_schema.sql

dump_schema() {
    echo "Dumping schema create script for database '$DB' into $SCHEMA_FILE"

    mysqldump \
        -h $MyHOST \
        -u $MyUSER \
        -p$MyPASS \
        --databases $DB \
        --add-drop-database \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --no-data \
        --skip-dump-date \
        --result-file=$SCHEMA_FILE \
        2> >(grep -v 'Using a password on the command line interface can be insecure')

    sed -i '' 's/DEFINER=`motdbuser`@`%`//g'  $SCHEMA_FILE
    sed -i '' 's/DEFINER=`mysql_admin`@`%`//g'  $SCHEMA_FILE

    echo "Database dumped '$DB' into $SCHEMA_FILE"
    echo
}

dump_static_data() {
    dump_data "static data" $STATIC_DATA_DIR $static_tables
}

dump_test_data() {
    dump_data "test data" $TEST_DATA_DIR $non_static_tables
}

dump_data() {
    local type=$1 # static data or test data
    local data_dir=$2
    shift 2
    local table_list=$@

    rm -rf $data_dir && mkdir -p $data_dir

    echo "Dumping populated $type tables into separate SQL command files for database '$DB' into dir=$data_dir"

    tbl_count=0

    for t in $(mysql -NBA -h $MyHOST -u $MyUSER -p$MyPASS -D $DB -e \
            "SELECT table_name FROM information_schema.TABLES
            WHERE TABLE_ROWS > 0
            AND TABLE_SCHEMA = '$DB'
            AND table_name in $table_list
            ;" 2> >(grep -v 'Using a password on the command line interface can be insecure'));
    do
        echo "DUMPING TABLE: $t"
        mysqldump \
            -h $MyHOST \
            -u $MyUSER \
            -p$MyPASS \
            $DB $t \
            --no-create-info \
            --skip-triggers \
            --single-transaction \
            --result-file=$data_dir/$t.sql \
            2> >(grep -v 'Using a password on the command line interface can be insecure')

        # remove lines that aren't the INSERT
        sed -i '' '/INSERT/!d' $data_dir/$t.sql

        # put each insert row on a new line
        sed -i '' 's/),(/),\'$'\n(/g' $data_dir/$t.sql
        sed -i '' 's/VALUES (/VALUES\'$'\n(/g' $data_dir/$t.sql

        tbl_count=$(( tbl_count + 1 ))
    done

    echo "$tbl_count $type tables dumped from database '$DB' into dir=$data_dir"
    echo
}

# Tables need to be in the static or non-static list in order to dump them to the right folder.
# this function checks that all tables are included in one of the lists
check_table_list() {

    unknown_tables=$(mysql -NBA -h $MyHOST -u $MyUSER -p$MyPASS -D $DB -e \
            "SELECT table_name FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '$DB'
            AND table_name NOT IN $static_tables
            AND table_name NOT IN $non_static_tables
            AND table_name NOT IN $history_tables
            AND table_name NOT IN $batch_tables
            ;" 2> >(grep -v 'Using a password on the command line interface can be insecure'));

    if [ -n "$unknown_tables" ]; then
        echo "It is unknown whether the following tables are static data or not, they might be newly added tables.
            Add them to one of the table lists in the script and run it again."
        for t in $unknown_tables
        do echo $t
        done

        exit 1
    fi
}

zip_up_dev_dir() {
    tar -czf dev-10k.tgz --exclude *releases* dev
}

# region Table name lists

# Note: these were manually divided up into the lists by checking if the '_hist' table exists and using best judgement.
#       So they may not be correct, feel free to move tables to the other list as you see fit.
static_tables="(
'app_auth_site_evidence_map',
'app_for_auth_for_ae',
'app_for_auth_testing_mot',
'app_for_auth_testing_mot_at_site',
'app_status',
'app_to_auth_testing_mot_at_site_map',
'application',
'approval_condition_appointment_map',
'assembly',
'assembly_role_type',
'assembly_type',
'auth_for_ae_status',
'auth_for_testing_mot_at_site_status',
'auth_for_testing_mot_role_map',
'auth_for_testing_mot_status',
'auth_status',
'body_type',
'brake_test_type',
'business_role_status',
'business_rule',
'business_rule_type',
'card_payment_token_usage',
'censor_blacklist',
'certificate_change_different_tester_reason_lookup',
'certificate_status',
'certificate_type',
'colour_lookup',
'company_type',
'configuration',
'contact_type',
'content_type',
'conviction',
'country_lookup',
'country_of_registration_lookup',
'cpms_notification',
'cpms_notification_scope',
'cpms_notification_status',
'cpms_notification_type',
'ctrl_sequence',
'database_version',
'db_upgrade',
'direct_debit_history_status',
'direct_debit_status',
'direct_debitory',
'direct_debitory_status',
'dvla_model_model_detail_code_map',
'dvla_make',
'dvla_model',
'dvla_vehicle_import_change_log',
'emergency_reason_lookup',
'empty_vin_reason_lookup',
'empty_vrm_reason_lookup',
'enforcement_condition_appointment_lookup',
'enforcement_decision_category_lookup',
'enforcement_decision_lookup',
'enforcement_decision_outcome_lookup',
'enforcement_decision_reinspection_outcome_lookup',
'enforcement_decision_score_lookup',
'enforcement_fuel_type_lookup',
'enforcement_full_partial_retest_lookup',
'enforcement_mot_demo_test',
'enforcement_visit_outcome_lookup',
'equipment_make',
'equipment_model',
'equipment_model_status',
'equipment_model_vehicle_class_link',
'equipment_status',
'equipment_type',
'event_category_lookup',
'event_outcome_lookup',
'event_type_lookup',
'event_type_outcome_category_map',
'evidence',
'experience',
'facility_type',
'failure_location_lookup',
'fuel_type',
'gender',
'identifying_token',
'incognito_vehicle',
'jasper_hard_copy',
'jasper_template',
'jasper_template_type',
'jasper_template_variation',
'language_type',
'licence_country_lookup',
'licence_type',
'make',
'message_content',
'message_type',
'message_url',
'model',
'mot1_vts_device_status',
'mot_test_event',
'mot_test_reason_for_cancel_lookup',
'mot_test_reason_for_refusal_lookup',
'mot_test_rfr_location_type',
'mot_test_rfr_map',
'mot_test_rfr_map_marked_as_repaired',
'mot_test_status',
'mot_test_survey',
'mot_test_type',
'non_working_day_country_lookup',
'non_working_day_lookup',
'notification_action_lookup',
'notification_action_map',
'notification_template',
'notification_template_action',
'odometer_reading',
'organisation_assembly_role_map',
'organisation_business_role',
'organisation_contact_type',
'organisation_site_status',
'organisation_type',
'payment_status',
'payment_type',
'permission',
'person_accesslog',
'person_auth_type_lookup',
'person_contact_type',
'person_identifying_token_map',
'person_security_question_answer',
'person_system_role',
'phone_contact_type',
'qualification',
'qualification_award',
'qualification_type',
'reason_for_rejection',
'reason_for_rejection_type',
'rfr_business_rule_map',
'rfr_language_content_map',
'rfr_vehicle_class_map',
'role',
'role_permission_map',
'security_question',
'security_card_status_lookup',
'site_business_role',
'site_condition_approval',
'site_contact_type',
'site_identifying_token_map',
'site_status_lookup',
'site_type',
'special_notice_audience',
'special_notice_audience_type',
'survey',
'test_item_category',
'test_item_category_vehicle_class_map',
'test_slot_transaction_amendment_reason',
'test_slot_transaction_amendment_type',
'test_slot_transaction_status',
'ti_category_language_content_map',
'title',
'token_lookup',
'transition_status',
'transmission_type',
'url_type',
'vehicle_class',
'vehicle_class_group',
'vehicle_detail_vw',
'vehicle_pre_refactor',
'visit',
'visit_reason_lookup',
'weight_source_lookup',
'wheelplan_type'
)"

non_static_tables="(
'address',
'authorised_examiner_principal',
'auth_for_ae',
'auth_for_ae_person_as_principal_map',
'auth_for_testing_mot_at_site',
'auth_for_testing_mot',
'brake_test_result_class_1_2',
'brake_test_result_class_3_and_above',
'brake_test_result_service_brake_data',
'certificate_replacement',
'comment',
'contact_detail',
'direct_debit',
'direct_debit',
'direct_debit_history',
'direct_debit',
'dvla_vehicle',
'email',
'emergency_log',
'empty_reason_map',
'enforcement_mot_test_differences',
'enforcement_mot_test_result',
'enforcement_mot_test_result_witnesses',
'enforcement_site_assessment',
'equipment',
'event',
'event_organisation_map',
'event_person_map',
'event_site_map',
'jasper_document',
'licence',
'message',
'model_detail',
'mot_test_address_comment',
'mot_test_cancelled',
'mot_test_complaint_ref',
'mot_test_current',
'mot_test_current_rfr_map',
'mot_test_emergency_reason',
'mot_test_history',
'mot_test_history_rfr_map',
'mot_test_rfr_map_comment',
'mot_test_rfr_map_custom_description',
'mot_test_rfr_map',
'notification_field',
'notification',
'odometer_reading',
'organisation_business_role_map',
'organisation_contact_detail_map',
'organisation',
'organisation_hist',
'organisation_site_map',
'password_detail',
'payment',
'permission_to_assign_role_map',
'person_contact_detail_map',
'person',
'person_security_card_map',
'person_security_question_map',
'person_system_role_map',
'phone',
'qualification_annual_certificate',
'security_card_drift',
'security_card',
'security_card_order',
'site_assembly_role_map',
'site_business_role_map',
'site_comment_map',
'site_contact_detail_map',
'site_emergency_log_map',
'site_facility',
'site',
'site_testing_daily_schedule',
'special_notice_content',
'special_notice_content_role_map',
'special_notice',
'test_slot_transaction_amendment',
'test_slot_transaction',
'user_filter',
'vehicle',
'vehicle_hist',
'vehicle_filter',
'vehicle_v5c'
)"

batch_tables="(
'BATCH_JOB_EXECUTION',
'BATCH_JOB_EXECUTION_CONTEXT',
'BATCH_JOB_EXECUTION_PARAMS',
'BATCH_JOB_EXECUTION_SEQ',
'BATCH_JOB_INSTANCE',
'BATCH_JOB_SEQ',
'BATCH_STEP_EXECUTION',
'BATCH_STEP_EXECUTION_CONTEXT',
'BATCH_STEP_EXECUTION_SEQ'
)"

history_tables="(
'body_type_hist',
'company_type_hist',
'configuration_hist',
'country_lookup_hist',
'dvla_model_model_detail_code_map_hist',
'dvla_make_hist',
'dvla_model_hist',
'notification_template_hist',
'permission_hist',
'person_system_role_hist',
'reason_for_rejection_hist',
'role_hist',
'role_permission_map_hist',
'security_card_status_lookup_hist',
'test_slot_transaction_amendment_reason_hist',
'test_slot_transaction_amendment_type_hist',
'address_hist',
'auth_for_ae_hist',
'auth_for_ae_person_as_principal_map_hist',
'auth_for_testing_mot_at_site_hist',
'auth_for_testing_mot_hist',
'brake_test_result_class_1_2_hist',
'brake_test_result_class_3_and_above_hist',
'brake_test_result_service_brake_data_hist',
'certificate_replacement_draft',
'certificate_replacement_draft_hist',
'certificate_replacement_hist',
'comment_hist',
'contact_detail_hist',
'direct_debit_hist',
'dvla_vehicle_hist',
'email_hist',
'emergency_log_hist',
'empty_reason_map_hist',
'enforcement_mot_test_result_hist',
'enforcement_mot_test_differences_hist',
'enforcement_site_assessment_hist',
'event_hist',
'event_organisation_map_hist',
'event_person_map_hist',
'event_site_map_hist',
'jasper_document_hist',
'message_hist',
'mot_test_current_hist',
'licence_hist',
'mot_test_address_comment_hist',
'mot_test_cancelled_hist',
'mot_test_complaint_ref_hist',
'mot_test_current_rfr_map_hist',
'mot_test_emergency_reason_hist',
'mot_test_history_rfr_map_hist',
'mot_test_rfr_map_comment_hist',
'mot_test_rfr_map_custom_description_hist',
'mot_test_rfr_map_hist',
'notification_hist',
'notification_field_hist',
'odometer_reading_hist',
'organisation_business_role_map_hist',
'organisation_site_map_hist',
'organisation_contact_detail_map_hist',
'payment_hist',
'password_detail_hist',
'person_hist',
'person_contact_detail_map_hist',
'person_security_card_map_hist',
'person_security_question_map_hist',
'person_system_role_map_hist',
'phone_hist',
'qualification_annual_certificate_hist',
'security_card_drift_hist',
'security_card_hist',
'security_card_order_hist',
'site_comment_map_hist',
'site_contact_detail_map_hist',
'site_emergency_log_map_hist',
'site_hist',
'site_facility_hist',
'site_testing_daily_schedule_hist',
'special_notice_content_hist',
'special_notice_content_role_map_hist',
'special_notice_hist',
'test_slot_transaction_amendment_hist',
'test_slot_transaction_hist',
'vehicle_hist_pre_refactor',
'mot_test_hist',
'mot_test_history_hist',
'site_business_role_map_hist',
'vehicle_v5c_hist'
)"
# endregion

# Run:
check_table_list
dump_schema
dump_static_data
dump_test_data

if [ "$TO_ZIP" = true ] ; then
    zip_up_dev_dir
fi

echo done
echo "Please remember to remove the mot-api/db/dev/releases folders as appropriate and
    test the reset database script if you are checking in these generated files."
