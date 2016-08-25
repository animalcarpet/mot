SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('Can order security card', 'CAN-ORDER-2FA-SECURITY-CARD', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

SET @can_order_security_card_permission = (SELECT `id` FROM `permission` WHERE `code` = 'CAN-ORDER-2FA-SECURITY-CARD');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  (
    (SELECT `id` FROM `role` WHERE `code` = 'TESTER'),
    @can_order_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'SITE-MANAGER'),
    @can_order_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'SITE-ADMIN'),
    @can_order_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DELEGATE'),
    @can_order_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'AUTHORISED-EXAMINER-DESIGNATED-MANAGER'),
    @can_order_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  );
