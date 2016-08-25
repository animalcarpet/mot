SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('Authenticate with 2FA', 'AUTHENTICATE-WITH-2FA', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

SET @authenticate_without_2fa_permission = (SELECT `id` FROM `permission` WHERE `code` = 'AUTHENTICATE-WITH-2FA');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  (
    (SELECT `id` FROM `role` WHERE `code` = 'SITE-ADMIN'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'SITE-MANAGER'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),(
    (SELECT `id` FROM `role` WHERE `code` = 'TESTER'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),(
    (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DESIGNATED-MANAGER'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),(
    (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DELEGATE'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),(
    (SELECT `id` FROM `role` WHERE `code` = 'TESTER-APPLICANT-DEMO-TEST-REQUIRED'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),(
    (SELECT `id` FROM `role` WHERE `code` = 'TESTER-ACTIVE'),
    @authenticate_without_2fa_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  );
