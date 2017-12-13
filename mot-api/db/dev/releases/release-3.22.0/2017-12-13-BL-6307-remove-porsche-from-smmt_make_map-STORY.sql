SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');


DELETE FROM
   `smmt_make_map`
WHERE
   make = 'PORSCHE';

