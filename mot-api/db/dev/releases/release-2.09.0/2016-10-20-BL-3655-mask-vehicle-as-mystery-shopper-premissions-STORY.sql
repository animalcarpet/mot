SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO
  `permission` (`name`, `code`, `is_restricted`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  ('Mask and unmask DVSA vehicles for mystery shopper', 'ENFORCEMENT-CAN-MASK-AND-UNMASK-VEHICLES', 0, @app_user_id, CURRENT_TIMESTAMP (6), @app_user_id, CURRENT_TIMESTAMP (6));

SET @update_vehicle = (SELECT `id` FROM `permission` WHERE `code` = 'ENFORCEMENT-CAN-MASK-AND-UNMASK-VEHICLES');

SET @a01 = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-AREA-OFFICE-1');
SET @ve = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');

INSERT INTO `role_permission_map`
(`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
(@ve, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@a01, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));