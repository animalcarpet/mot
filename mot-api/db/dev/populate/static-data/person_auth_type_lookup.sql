-- insert two current forms of supported 2FA
INSERT into `person_auth_type_lookup` (`id`, `name`, `code`, `created_by`, `version`) VALUES
  (1, 'Pin', 'PIN', @app_user_id, 1),
  (2, 'Card', 'CARD', @app_user_id, 1);

