SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `security_card_status_lookup`
(`code`, `name`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`, `version`)
VALUES
  ('ASGND', 'Assigned', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
  ,('UNASD', 'Unassigned', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
  ,('DEACT', 'Deactivated', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1);


