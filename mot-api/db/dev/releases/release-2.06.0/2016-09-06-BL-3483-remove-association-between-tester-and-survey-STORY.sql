SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

ALTER TABLE `mot_test_survey` DROP FOREIGN KEY `fk_survey_id_survey_id`;
ALTER TABLE `mot_test_survey` DROP `survey_id`;

ALTER TABLE `mot_test_survey` ADD COLUMN `has_been_presented` TINYINT NOT NULL DEFAULT 0 AFTER `token`;
ALTER TABLE `mot_test_survey` ADD COLUMN `has_been_submitted` TINYINT NOT NULL DEFAULT 0 AFTER `has_been_presented`;

ALTER TABLE `survey` MODIFY `created_on` DATE NOT NULL;
ALTER TABLE `survey` DROP `last_updated_on`;