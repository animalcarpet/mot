SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

UPDATE `special_notice_content`
SET `is_deleted` = 0
WHERE (`issue_number` IN (3, 5, 6, 7)
  AND `issue_year` = 2016);

UPDATE `special_notice`
JOIN `special_notice_content`
  ON `special_notice`.`special_notice_content_id` = `special_notice_content`.`id`
SET `special_notice`.`is_deleted` = 0
WHERE (`special_notice_content`.`issue_number` IN (3, 5, 6, 7)
  AND `special_notice_content`.`issue_year` = 2016);