SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `notification_template` (`id`, `content`, `subject`, `created_by`, `last_updated_by`, `last_updated_on`)
VALUES
(
  33,
  'Your security card was ordered at ${dateTimeOrdered} and is on its way.\n\nActivate the card from your profile, as soon as it arrives.\n\nDidn\'t order a security card?\n\nContact the DVSA helpdesk on 0330 123 5654\n\nOpening hours: \n Monday to Friday, 8am to 8pm\nSaturday, 8am to 2pm\nSunday, closed',
  'You have ordered a security card',
  @app_user_id,
  @app_user_id,
  CURRENT_TIMESTAMP(6)
);