SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `reason_for_rejection_type`(`name`, `code`, `description`, `created_by`)
values('MINOR', 'M', 'Post EU Minor Defect', @app_user_id);