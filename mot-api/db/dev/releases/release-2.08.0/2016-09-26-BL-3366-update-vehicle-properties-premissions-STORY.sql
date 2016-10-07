-- this sql inserts the same permissions as same removed file in 2.07 but also checks if role-permission assignments are not existing
-- this file should initially go only to 2.08, but was already promoted to ACPT from 2.07
-- that's the reason to check existence of role-permission connections so it won't cause trouble on FB's and ACPT
SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

SET @update_vehicle = (SELECT `id` FROM `permission` WHERE `code` = 'VEHICLE-UPDATE');

-- DVSA
SET @scheme_mgr = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-MANAGEMENT');
SET @scheme_user = (SELECT `id` FROM `role` WHERE `code` = 'DVSA-SCHEME-USER');
SET @ve = (SELECT `id` FROM `role` WHERE `code` = 'VEHICLE-EXAMINER');

-- Trade
SET @tester = (SELECT `id` FROM `role` WHERE `code` = 'TESTER');


DROP PROCEDURE IF EXISTS update_vehicle_properties;

DELIMITER ;;

CREATE PROCEDURE update_vehicle_properties()
  BEGIN
  DECLARE alreadyAssignedRolesCount INTEGER;
  SELECT COUNT(*) FROM role_permission_map WHERE permission_id = @update_vehicle AND role_id IN(@scheme_mgr, @scheme_user, @ve, @tester) INTO alreadyAssignedRolesCount;
    IF (alreadyAssignedRolesCount = 0)
    THEN
      INSERT INTO `role_permission_map`
      (`role_id`, `permission_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
      VALUES
      (@scheme_mgr, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@scheme_user, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@ve, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@tester, @update_vehicle, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));
      END IF;
END;;

DELIMITER ;

CALL update_vehicle_properties();

DROP PROCEDURE update_vehicle_properties;