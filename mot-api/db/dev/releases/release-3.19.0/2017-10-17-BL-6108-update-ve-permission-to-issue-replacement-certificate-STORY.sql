SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');
SET @app_role = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');

INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`)
VALUES
(
  @app_role,
  (SELECT `id` FROM `permission` WHERE `code` = "CERTIFICATE-REPLACEMENT"),
  @app_user_id
),
(
  @app_role,
  (SELECT `id` FROM `permission` WHERE `code` = "CERTIFICATE-REPLACEMENT-FULL"),
  @app_user_id
),
(
  @app_role,
  (SELECT `id` FROM `permission` WHERE `code` = "CERTIFICATE-REPLACEMENT-SPECIAL-FIELDS"),
  @app_user_id
);