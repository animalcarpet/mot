SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`
  (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  ('View tests of type non-MOT test in MOT history', 'VIEW-NON-MOT-TESTS', 0, @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6)),
  ('View tests of type mystery shopper in MOT history', 'VIEW-MYSTERY-SHOPPER-TESTS', 0, @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));

SET @view_non_mot_tests_permission = (SELECT `id` FROM `permission` WHERE `code` = 'VIEW-NON-MOT-TESTS');
SET @view_mystery_shopper_tests_permission = (SELECT `id` FROM `permission` WHERE `code` = 'VIEW-MYSTERY-SHOPPER-TESTS');

SET @ve = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');
SET @ao1 = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-1');

INSERT INTO `role_permission_map`
  (`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  (@ve, @view_non_mot_tests_permission, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ao1, @view_non_mot_tests_permission, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ve, @view_mystery_shopper_tests_permission, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (@ao1, @view_mystery_shopper_tests_permission, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));