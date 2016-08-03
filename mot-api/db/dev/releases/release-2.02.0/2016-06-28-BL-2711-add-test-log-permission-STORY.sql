SET @app_user_id = (SELECT `id`
                    FROM `person`
                    WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO
  `permission` (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  ('View Test Logs for Tester', 'TESTER-VIEW-TEST-LOGS', 0, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id,
   CURRENT_TIMESTAMP(6));


INSERT INTO `role_permission_map`
(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  ((SELECT `id` FROM `role` WHERE `code` = 'TESTER'),
   (SELECT `id` FROM `permission` WHERE `code` = 'TESTER-VIEW-TEST-LOGS'),
   @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));