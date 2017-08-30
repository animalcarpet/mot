SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `permission` (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES ('View Average Test Quality for VTS', 'VTS-VIEW-AVERAGE-TEST-QUALITY', 0, @app_user_id,  CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));

SET @permission_id = (SELECT `id` FROM `permission` WHERE `code` = "VTS-VIEW-AVERAGE-TEST-QUALITY");

INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`)
VALUES
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-SCHEME-MANAGEMENT"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-SCHEME-USER"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-AREA-OFFICE-1"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "VEHICLE-EXAMINER"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "AUTHORISED-EXAMINER-DESIGNATED-MANAGER"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "AUTHORISED-EXAMINER-DELEGATE"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "SITE-MANAGER"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "SITE-ADMIN"),
  @permission_id,
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "TESTER"),
  @permission_id,
  @app_user_id
);