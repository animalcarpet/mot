SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('Can view other 2FA security card', 'CAN-VIEW-OTHER-2FA-SECURITY-CARD', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

SET @can_view_other_2fa_security_card_permission = (SELECT `id` FROM `permission` WHERE `code` = 'CAN-VIEW-OTHER-2FA-SECURITY-CARD');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  (
    (SELECT `id` FROM `role` WHERE `code` = 'CUSTOMER-SERVICE-CENTRE-OPERATIVE'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'CUSTOMER-SERVICE-MANAGER'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-1'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  ),
  (
    (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER'),
    @can_view_other_2fa_security_card_permission,
    @app_user_id,
    CURRENT_TIMESTAMP(6),
    @app_user_id,
    CURRENT_TIMESTAMP(6)
  );
