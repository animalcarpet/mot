SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('View security card order', 'VIEW-SECURITY-CARD-ORDER', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

INSERT INTO `permission`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`) VALUES
  ('manage role dvsa central admin team', 'MANAGE-ROLE-CENTRAL-ADMIN-TEAM', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

INSERT INTO `role`(`name`, `code`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`, `is_internal`) VALUES
  ('Central admin team', 'CENTRAL-ADMIN-TEAM', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1);

SET @central_admin_team_role_id = (SELECT `id` FROM `role` WHERE `code` = 'CENTRAL-ADMIN-TEAM');

SET @view_security_card_order_permission_id = (SELECT `id` FROM `permission` WHERE `code` = 'VIEW-SECURITY-CARD-ORDER');

SET @manage_role_dvsa_central_admin_team_permission_id = (SELECT `id` FROM `permission` WHERE `code` = 'MANAGE-ROLE-CENTRAL-ADMIN-TEAM');

SET @dvsa_scheme_management_role_id = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT');

SET @dvsa_scheme_user_role_id = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER');

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`) VALUES (
  @central_admin_team_role_id,
  @view_security_card_order_permission_id,
  @app_user_id
);

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`) VALUES (
  @dvsa_scheme_management_role_id,
  @manage_role_dvsa_central_admin_team_permission_id,
  @app_user_id
);

INSERT INTO `role_permission_map`(`role_id`, `permission_id`, `created_by`) VALUES (
  @dvsa_scheme_user_role_id,
  @manage_role_dvsa_central_admin_team_permission_id,
  @app_user_id
);

INSERT INTO `person_system_role`(`name`, `full_name`, `short_name`, `role_id`, `created_by`) VALUES (
  'CENTRAL-ADMIN-TEAM',
  'Central admin team',
  'CAT',
  @central_admin_team_role_id,
  @app_user_id
);

INSERT INTO `permission_to_assign_role_map`(`permission_id`, `role_id`, `created_by`) VALUES (
  @manage_role_dvsa_central_admin_team_permission_id,
  @central_admin_team_role_id,
  @app_user_id
);
