DELIMITER $$
DROP PROCEDURE IF EXISTS `mot2`.`add_vehicle_hist_entry`$$

CREATE PROCEDURE `mot2`.`add_vehicle_hist_entry`(INOUT p_version INTEGER UNSIGNED,
                                                 IN p_vehicle_id INTEGER UNSIGNED,
                                                 IN p_registration VARCHAR(20),
                                                 IN p_vin VARCHAR(30),
                                                 IN p_model_detail_id INTEGER UNSIGNED,
                                                 IN p_year YEAR,
                                                 IN p_manufacture_date DATE,
                                                 IN p_first_registration_date DATE,
                                                 IN p_first_used_date DATE,
                                                 IN p_primary_colour_id SMALLINT UNSIGNED,
                                                 IN p_secondary_colour_id SMALLINT UNSIGNED,
                                                 IN p_weight INTEGER UNSIGNED,
                                                 IN p_weight_source_id SMALLINT UNSIGNED,
                                                 IN p_country_of_registration_id SMALLINT UNSIGNED,
                                                 IN p_engine_number VARCHAR(30),
                                                 IN p_chassis_number VARCHAR(30),
                                                 IN p_is_new_at_first_reg TINYINT UNSIGNED,
                                                 IN p_dvla_vehicle_id INTEGER UNSIGNED,
                                                 IN p_is_damaged TINYINT UNSIGNED,
                                                 IN p_is_destroyed TINYINT UNSIGNED,
                                                 IN p_is_incognito TINYINT UNSIGNED)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
    /**
    * Write a row to `mot2`.`vehicle_hist`. Identifies intermediate vehicle version number for the record, and returns
    * that value as an INOUT parameter to the caller. The _hist tables do not have their own triggers so we do not need
    * to protect against them firing.
    *
    * It is the responsibility of the caller to wrap this call in a transaction to provide it with a consistent view of
    * the vehicle_hist table. Failure to do so could result in returning a version number that is no longer valid at the
    * point of the caller consuming it.
    *
    * Example:
    *   SET @version := 10000; -- this is the 'current' version
    *   CALL `mot2`.`add_vehicle_hist_entry`(@version, 100, 'S130XET', 'WF0BXXWPRBWY29630', 361, 1998, '1998-12-31', '1998-12-31', '1998-12-31', 7, 19,
    *                                                  1010, 1, 1, 'WY29630', NULL, TRUE, 78539677, FALSE, TRUE, FALSE);
    *   SELECT @version `New Version Number`;
    *
    * param     p_version as an INOUT parameter returns the version number of the incoming vehicle record. It must be
    *             set to the value expected to be 'below' the newly written version of the vehicle's history. E.g. pass
    *             as 10000 and expect a return of something along the lines of 15000, 12500, 11250, or 10625, etc.
    *             This cannot be the current vehicle version number. The process does not check to see if this is an
    *             actual version number, as there is no real need to.
    *
    * throws    '45000' if @app_user_id is NULL.
    * throws    '45000' if p_version is NULL, or zero.
    * throws    '45000' if p_is is NULL _or_ both p_registration and p_vin are NULL.
    * throws    '45000' if p_version is higher or equal to the vehicle's current (latest) version number.
    * throws    '45000' if it is unable to establish the version number that appears immediately above the one we're
    *                     altering. E.g. if the current vehicle version is 20000, and we alter 10000 then it should offer
    *                     (say) 15000.
    * throws    '45000' if the calculated new version is the same (though the course of division) as the version number
    *                     passed by the caller.
    */
    DECLARE c_module VARCHAR(64) DEFAULT 'add_vehicle_hist_entry';
    DECLARE c_version VARCHAR(256) DEFAULT '$Id$';

    DECLARE c_incoming_vehicle_version INTEGER UNSIGNED DEFAULT IFNULL(p_version, 0); -- careful around naming here as we already have a 'c_version'
    DECLARE c_version_midpoint INTEGER UNSIGNED DEFAULT 5000;

    DECLARE c_registration_collapsed VARCHAR(20) DEFAULT `mot2`.`f_collapse`(p_registration);
    DECLARE c_vin_collapsed VARCHAR(30) DEFAULT `mot2`.`f_collapse`(p_vin);

    DECLARE c_is_new_at_first_reg TINYINT UNSIGNED DEFAULT IFNULL(p_is_new_at_first_reg, FALSE);
    DECLARE c_is_damaged TINYINT UNSIGNED DEFAULT IFNULL(p_is_damaged, FALSE);
    DECLARE c_is_destroyed TINYINT UNSIGNED DEFAULT IFNULL(p_is_destroyed, FALSE);
    DECLARE c_is_incognito TINYINT UNSIGNED DEFAULT IFNULL(p_is_incognito, FALSE);

    DECLARE c_current_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

    DECLARE v_current_vehicle_version INTEGER UNSIGNED DEFAULT 0;
    DECLARE v_lowest_version_above INTEGER UNSIGNED DEFAULT 0;
    DECLARE v_highest_version_below INTEGER UNSIGNED DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        DECLARE c_null CHAR(4) CHARACTER SET latin1 DEFAULT 'NULL';
        DECLARE v_returned_sqlstate, v_message_text VARCHAR(1024) DEFAULT '';
        GET DIAGNOSTICS CONDITION 1 v_returned_sqlstate = RETURNED_SQLSTATE, v_message_text = MESSAGE_TEXT;
        CALL `ddr_util`.`logger`('error', c_module, CONCAT_WS('|', v_returned_sqlstate, v_message_text,
                                                              IFNULL(p_version, c_null),
                                                              IFNULL(p_vehicle_id, c_null),
                                                              IFNULL(p_registration, c_null),
                                                              IFNULL(p_vin, c_null),
                                                              IFNULL(p_model_detail_id, c_null),
                                                              IFNULL(p_year, c_null),
                                                              IFNULL(p_manufacture_date, c_null),
                                                              IFNULL(p_first_registration_date, c_null),
                                                              IFNULL(p_first_used_date, c_null),
                                                              IFNULL(p_primary_colour_id, c_null),
                                                              IFNULL(p_secondary_colour_id, c_null),
                                                              IFNULL(p_weight, c_null),
                                                              IFNULL(p_weight_source_id, c_null),
                                                              IFNULL(p_country_of_registration_id, c_null),
                                                              IFNULL(p_engine_number, c_null),
                                                              IFNULL(p_chassis_number, c_null),
                                                              IFNULL(p_is_new_at_first_reg, c_null),
                                                              IFNULL(p_dvla_vehicle_id, c_null),
                                                              IFNULL(p_is_damaged, c_null),
                                                              IFNULL(p_is_destroyed, c_null),
                                                              IFNULL(p_is_incognito, c_null)));
        RESIGNAL;
    END;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL.';
    END IF;

    IF (c_incoming_vehicle_version = 0)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Incoming Vehicle Version cannot be passed as NULL or 0 (zero)';
    END IF;

    IF (p_vehicle_id IS NULL OR (p_registration IS NULL AND p_vin IS NULL))
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'One or more key parameters are set to NULL';
    END IF;

    SELECT `version` INTO v_current_vehicle_version
      FROM `mot2`.`vehicle`
     WHERE `id` = p_vehicle_id;

    IF (FOUND_ROWS() = 0)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehicle does not exist';
    END IF;

    IF (v_current_vehicle_version < p_version)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New version is too high';
    END IF;

    -- locate the vehicle's historical version number that appears immediately above the version we're 'updating'.
    -- if the version we're updating is the highest hist version then we need to position between that and the current
    -- vehicle.version value
    SELECT IFNULL(MIN(`version`), v_current_vehicle_version) INTO v_lowest_version_above
      FROM `mot2`.`vehicle_hist`
     WHERE `id` = p_vehicle_id
       AND `version` > p_version;

    SELECT MAX(`version`) INTO v_highest_version_below
      FROM `mot2`.`vehicle_hist`
     WHERE `id` = p_vehicle_id
       AND `version` < p_version;

    IF (IFNULL(v_lowest_version_above, 0) = 0)
    THEN
        -- it should not be possible for this condition to occur, but if it does, and we ignore it, then we'll have problems
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Could not establish the version immediately after the one specified';
    END IF;

    IF ((v_lowest_version_above - p_version) = 0)
    THEN
        IF (IFNULL(v_highest_version_below, 0) = 0)
        THEN
            SET p_version := c_version_midpoint;
        ELSE
            SET p_version := v_highest_version_below + ((p_version - v_highest_version_below) / 2);
        END IF;
        ELSE
            SET p_version := p_version + ((v_lowest_version_above - p_version) / 2); -- this is the new version number
    END IF;

    IF (p_version = v_lowest_version_above) OR (p_version = v_highest_version_below)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not enough version numbers available';
    END IF;

    -- LAST_INSERT_ID() to get `hist_id` back, but we're not interested in that.
    INSERT INTO `mot2`.`vehicle_hist` (hist_id, expired_on, expired_by,
                                       id, registration, registration_collapsed, vin, vin_collapsed, model_detail_id,
                                       year, manufacture_date, first_registration_date, first_used_date,
                                       primary_colour_id, secondary_colour_id,
                                       weight, weight_source_id, country_of_registration_id,
                                       engine_number, chassis_number,
                                       is_new_at_first_reg, dvla_vehicle_id, is_damaged, is_destroyed, is_incognito,
                                       created_by, created_on, last_updated_by, last_updated_on, version)
                               VALUES (NULL, c_current_timestamp, @app_user_id,
                                       p_vehicle_id, p_registration, c_registration_collapsed, p_vin, c_vin_collapsed, p_model_detail_id,
                                       p_year, p_manufacture_date, p_first_registration_date, p_first_used_date,
                                       p_primary_colour_id, p_secondary_colour_id,
                                       p_weight, p_weight_source_id, p_country_of_registration_id,
                                       p_engine_number, p_chassis_number,
                                       c_is_new_at_first_reg, p_dvla_vehicle_id, c_is_damaged, c_is_destroyed, c_is_incognito,
                                       @app_user_id, c_current_timestamp, @app_user_id, c_current_timestamp, p_version);
END$$

DELIMITER ;