SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`)
VALUES
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-AREA-OFFICE-1"),
  (SELECT `id` FROM `permission` WHERE `code` = "TESTER-READ-OTHERS"),
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-AREA-OFFICE-1"),
  (SELECT `id` FROM `permission` WHERE `code` = "MOT-TEST-COMPARE"),
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "DVSA-AREA-OFFICE-1"),
  (SELECT `id` FROM `permission` WHERE `code` = "MOT-TEST-REINSPECTION-PERFORM"),
  @app_user_id
);