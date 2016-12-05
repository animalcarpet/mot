-- Reset flag to false before mystery shopper epic will be turned ON on PROD
SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

UPDATE vehicle
SET    is_incognito = FALSE
WHERE  is_incognito = TRUE;