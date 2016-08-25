SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `person_system_role_map` (`person_system_role_id`, `status_id`, `person_id`, `created_by`) VALUES (
  (SELECT `id` FROM `person_system_role` WHERE `name` = 'CENTRAL-ADMIN-TEAM'),
  (SELECT `id` FROM `business_role_status` WHERE `code` = 'AC'),
  (SELECT `id` FROM `person` WHERE `username` = 'QCUser'),
  @app_user_id
);