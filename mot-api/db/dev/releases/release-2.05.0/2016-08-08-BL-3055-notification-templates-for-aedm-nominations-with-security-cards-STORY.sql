SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `notification_template` (`id`, `content`, `subject`, `created_by`, `last_updated_by`, `last_updated_on`)
VALUES
(
  31,
  'You have been nominated for ${positionName} at ${organisationName}.\nYou need to order a security card.\nOnce you have activated the card, you will receive the role.',
  'Nomination for ${positionName} - order a security card',
  @app_user_id,
  @app_user_id,
  CURRENT_TIMESTAMP(6)
),
(
  32,
  'You will receive the role of ${positionName} at ${organisationName} once you have activated the card.',
  'Nomination for ${positionName} - activate your security card',
  @app_user_id,
  @app_user_id,
  CURRENT_TIMESTAMP(6)
);
