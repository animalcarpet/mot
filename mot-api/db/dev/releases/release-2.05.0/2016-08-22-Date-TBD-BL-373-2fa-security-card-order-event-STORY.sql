SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `event_type_lookup` (`code`, `name`, `display_order`, `start_date`, `created_by`,`created_on`, `last_updated_by`, `last_updated_on`)
VALUES
('SCO', 'Security Card Order', null, '1900-01-01', @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));
