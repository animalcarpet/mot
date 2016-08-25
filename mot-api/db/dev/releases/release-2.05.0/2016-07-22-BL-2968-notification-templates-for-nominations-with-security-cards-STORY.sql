SET @app_user_id = (SELECT id FROM person WHERE username = 'static data');

INSERT INTO `notification_template` (`id`, `content`, `subject`, `created_by`, `last_updated_by`, `last_updated_on`)
VALUES
(
  29,
  'You have been nominated for ${positionName} at ${siteName}.\nYou need to order a security card.\nOnce you have activated the card, you can accept or reject the role.',
  'Nomination for ${positionName} - order a security card',
  @app_user_id,
  @app_user_id,
  CURRENT_TIMESTAMP(6)
),
(
  30,
  'You can accept or reject the role of ${positionName} at ${siteName} once you have activated the card.',
  'Nomination for ${positionName} - activate your security card',
  @app_user_id,
  @app_user_id,
  CURRENT_TIMESTAMP(6)
);
