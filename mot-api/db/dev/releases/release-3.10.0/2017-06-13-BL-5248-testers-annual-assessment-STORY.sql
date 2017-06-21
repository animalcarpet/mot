SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

SET @permission_name = 'TESTERS-ANNUAL-ASSESSMENT-VIEW';

INSERT INTO `permission` (
    `name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`
)
VALUES (
  'View testers annual assessment', @permission_name, 0, @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6)
);

SET @testers_annual_assessment = (SELECT `id` FROM `permission` WHERE `code` = @permission_name);

-- DVSA
SET @scheme_mgr = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT');
SET @scheme_user = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER');
SET @ao1 = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-1');
SET @ve = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');
SET @csco = (SELECT `id` FROM `role` WHERE `code` = 'CUSTOMER-SERVICE-CENTRE-OPERATIVE');

-- Trade
SET @aedm = (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DESIGNATED-MANAGER');
SET @aed = (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DELEGATE');
SET @site_manager = (SELECT `id` FROM `role` WHERE `code` = 'SITE-MANAGER');
SET @site_admin = (SELECT `id` FROM `role` WHERE `code` = 'SITE-ADMIN');


INSERT INTO `role_permission_map` (
  `role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`
)
VALUES
  (@scheme_mgr, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@scheme_user, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ao1, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ve, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@csco, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@aedm, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@site_manager, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@site_admin, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@aed, @testers_annual_assessment, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));