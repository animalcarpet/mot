SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

SET @max_display_order = (SELECT max(`display_order`) from `mot_test_type`);

INSERT INTO `mot_test_type` (`code`, `description`, `display_order`, `is_demo`, `is_slot_consuming`, `is_reinspection`, `created_by`)
	(SELECT 'MS' as `code`, 'Mystery Shopper' as `description`, @max_display_order + 1, `is_demo`, `is_slot_consuming`,`is_reinspection`, @app_user_id as `created_by`
	FROM `mot_test_type`
	WHERE `code` = 'NT');