SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

UPDATE `configuration`
SET `value` = "28"
WHERE `key` = 'odometerReadingModificationWindowLengthInDays';
