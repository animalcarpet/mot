SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `permission` (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES ('Send monthly notification about TQI', 'NOTIFY-AEDM-AND-AED-ABOUT-TQI-STATS', 0, @app_user_id,  CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));

SET @permission_id = (SELECT `id` FROM `permission` WHERE `code` = "NOTIFY-AEDM-AND-AED-ABOUT-TQI-STATS");

INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`)
VALUES
(
  (SELECT `id` FROM `role` WHERE `code` = "CRON"),
  @permission_id,
  @app_user_id
);