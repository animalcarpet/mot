SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('Generate a satisfaction survey report', 'GENERATE-SATISFACTION-SURVEY-REPORT', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

SET @generate_satisfaction_survey_report_permission_id = (SELECT `id` FROM `permission` WHERE `code` = 'GENERATE-SATISFACTION-SURVEY-REPORT');
SET @dvsa_scheme_management_role_id = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT');
SET @dvsa_scheme_user_role_id = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`) VALUES (
  @dvsa_scheme_management_role_id,
  @generate_satisfaction_survey_report_permission_id,
  @app_user_id
);

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`) VALUES (
  @dvsa_scheme_user_role_id,
  @generate_satisfaction_survey_report_permission_id,
  @app_user_id
);