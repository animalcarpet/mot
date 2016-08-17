SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO
  `permission` (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  ('View Test quality for Tester', 'TESTER-VIEW-TEST-QUALITY', 0, @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));

SET @tester_view_tqi = (SELECT `id` FROM `permission` WHERE `code` = 'TESTER-VIEW-TEST-QUALITY');

-- DVSA
SET @scheme_mgr = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT');
SET @scheme_user = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER');
SET @ao1 = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-1');
SET @ao2 = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-2');
SET @ve = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');

INSERT INTO `role_permission_map`
(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  (@scheme_mgr, @tester_view_tqi, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@scheme_user, @tester_view_tqi, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ao1, @tester_view_tqi, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ao2, @tester_view_tqi, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ve, @tester_view_tqi, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));