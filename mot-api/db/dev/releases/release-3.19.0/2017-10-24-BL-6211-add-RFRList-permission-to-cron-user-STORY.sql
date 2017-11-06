SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

SET @role_name = 'cron';

SET @rfr_list_permision_id = (SELECT `id` FROM `permission` WHERE `code` = 'RFR-LIST');
SET @cron_role_id = (SELECT id from role where name=@role_name);

#Create role permission map
INSERT INTO `role_permission_map` (`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES (@cron_role_id, @rfr_list_permision_id, @app_user_id,  CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));
