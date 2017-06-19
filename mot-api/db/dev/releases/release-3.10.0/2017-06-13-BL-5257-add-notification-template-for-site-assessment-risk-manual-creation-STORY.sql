SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `notification_template` (`id`, `content`, `subject`, `created_by`,`created_on`, `last_updated_by`, `last_updated_on`)
VALUES
(
  34,
  'A new site assessment has been recorded for ${siteNumber} ${siteName}.',
  'Site assessment update',
  @app_user_id,
  CURRENT_TIMESTAMP (6),
  @app_user_id,
  CURRENT_TIMESTAMP (6)
);
