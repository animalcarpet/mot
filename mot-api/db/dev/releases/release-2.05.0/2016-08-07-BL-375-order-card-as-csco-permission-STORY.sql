SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('Can order security card for another user', 'CAN-ORDER-2FA-SECURITY-CARD-FOR-OTHER-USER', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

SET @can_order_security_card_for_others_permission = (SELECT `id` FROM `permission` WHERE `code` = 'CAN-ORDER-2FA-SECURITY-CARD-FOR-OTHER-USER');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  (
    (SELECT `id` FROM `role` WHERE `code` = 'CUSTOMER-SERVICE-CENTRE-OPERATIVE'),
    @can_order_security_card_for_others_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'CUSTOMER-SERVICE-MANAGER'),
    @can_order_security_card_for_others_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  );
