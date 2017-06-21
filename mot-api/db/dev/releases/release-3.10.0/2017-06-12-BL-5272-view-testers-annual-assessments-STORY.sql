SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');


INSERT INTO `permission` (`name`, `code`, `created_by`)
VALUES
(
  "View Mot Testing Annual Certificate For Site User",
  "VIEW-MOT-TESTING-ANNUAL-CERTIFICATE-FOR-SITE-USER",
  @app_user_id
);


INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`)
VALUES
(
  (SELECT `id` FROM `role` WHERE `code` = "SITE-ADMIN"),
  (SELECT `id` FROM `permission` WHERE `code` = "VIEW-MOT-TESTING-ANNUAL-CERTIFICATE-FOR-SITE-USER"),
  @app_user_id
),
(
  (SELECT `id` FROM `role` WHERE `code` = "CUSTOMER-SERVICE-CENTRE-OPERATIVE"),
  (SELECT `id` FROM `permission` WHERE `code` = "VIEW-MOT-TESTING-ANNUAL-CERTIFICATE-FOR-USER"),
  @app_user_id
);
