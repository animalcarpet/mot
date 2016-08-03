SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

UPDATE configuration
SET `value` = '35116,00000',
    `last_updated_by` = @app_user_id, `last_updated_on` = CURRENT_TIMESTAMP(6)
WHERE `key` = 'testSlotCostCentre';