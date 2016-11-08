SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

UPDATE `company_type`
 SET `name` = 'Sole trader'
 WHERE `code` = 'ST';

UPDATE `company_type`
 SET `name` = 'Limited liability partnership'
 WHERE `code` = 'LLP';

UPDATE `company_type`
 SET `name` = 'Public body'
 WHERE `code` = 'PA';