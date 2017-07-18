SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

UPDATE `configuration`
SET `value` = 'MOT Slots'
WHERE `key` = 'testSlotProductDescription';