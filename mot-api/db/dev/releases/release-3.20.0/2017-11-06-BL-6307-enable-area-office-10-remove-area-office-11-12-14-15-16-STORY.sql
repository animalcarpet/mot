SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

-- Set Area Office 10 to Approved
UPDATE `site` SET site_status_id=1 WHERE site_number='10';

-- Set Area Office 11, 12, 14, 15 & 16 to Extinct
UPDATE `site` SET site_status_id=11 WHERE site_number IN ('11', '12', '14', '15', '16');

