SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

ALTER TABLE reason_for_rejection
CHANGE COLUMN inspection_manual_reference inspection_manual_reference varchar(18) ;

ALTER TABLE reason_for_rejection_hist
CHANGE COLUMN inspection_manual_reference inspection_manual_reference varchar(18) ;

ALTER TABLE `brake_test_result_service_brake_data`
  ADD `is_steered_axle1` TINYINT(4) AFTER `imbalance_pass`,
  ADD `is_steered_axle2` TINYINT(4) AFTER `is_steered_axle1`,
  ADD `is_steered_axle3` TINYINT(4) AFTER `is_steered_axle2`;


ALTER TABLE `brake_test_result_service_brake_data_hist`
  ADD `is_steered_axle1` TINYINT(4) AFTER `imbalance_pass`,
  ADD `is_steered_axle2` TINYINT(4) AFTER `is_steered_axle1`,
  ADD `is_steered_axle3` TINYINT(4) AFTER `is_steered_axle2`;

DROP TRIGGER IF EXISTS tr_brake_test_result_service_brake_data_bd;
DROP TRIGGER IF EXISTS tr_brake_test_result_service_brake_data_au;

DELIMITER $$

CREATE TRIGGER `mot2`.`tr_brake_test_result_service_brake_data_bd`
BEFORE DELETE ON `mot2`.`brake_test_result_service_brake_data`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_brake_test_result_service_brake_data_bd Generated on 2017-12-14 17:05:51
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_brake_test_result_service_brake_data_bd Generated on 2017-12-14 17:05:51. $Id$';
    
    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;
    
    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;
    
    INSERT INTO `mot2`.`brake_test_result_service_brake_data_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`effort_nearside_axle1`
        ,`effort_offside_axle1`
        ,`effort_nearside_axle2`
        ,`effort_offside_axle2`
        ,`effort_nearside_axle3`
        ,`effort_offside_axle3`
        ,`effort_single`
        ,`lock_nearside_axle1`
        ,`lock_offside_axle1`
        ,`lock_nearside_axle2`
        ,`lock_offside_axle2`
        ,`lock_nearside_axle3`
        ,`lock_offside_axle3`
        ,`lock_single`
        ,`imbalance_axle1`
        ,`imbalance_axle2`
        ,`imbalance_axle3`
        ,`imbalance_pass`
        ,`is_steered_axle1`
        ,`is_steered_axle2`
        ,`is_steered_axle3`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`effort_nearside_axle1`
        ,OLD.`effort_offside_axle1`
        ,OLD.`effort_nearside_axle2`
        ,OLD.`effort_offside_axle2`
        ,OLD.`effort_nearside_axle3`
        ,OLD.`effort_offside_axle3`
        ,OLD.`effort_single`
        ,OLD.`lock_nearside_axle1`
        ,OLD.`lock_offside_axle1`
        ,OLD.`lock_nearside_axle2`
        ,OLD.`lock_offside_axle2`
        ,OLD.`lock_nearside_axle3`
        ,OLD.`lock_offside_axle3`
        ,OLD.`lock_single`
        ,OLD.`imbalance_axle1`
        ,OLD.`imbalance_axle2`
        ,OLD.`imbalance_axle3`
        ,OLD.`imbalance_pass`
        ,OLD.`is_steered_axle1`
        ,OLD.`is_steered_axle2`
        ,OLD.`is_steered_axle3`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;

$$

CREATE TRIGGER `mot2`.`tr_brake_test_result_service_brake_data_au`
AFTER UPDATE ON `mot2`.`brake_test_result_service_brake_data`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_brake_test_result_service_brake_data_au Generated on 2017-12-14 17:05:51
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_brake_test_result_service_brake_data_au Generated on 2017-12-14 17:05:51. $Id$';
    
    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;
    
    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;
    
    INSERT INTO `mot2`.`brake_test_result_service_brake_data_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`effort_nearside_axle1`
        ,`effort_offside_axle1`
        ,`effort_nearside_axle2`
        ,`effort_offside_axle2`
        ,`effort_nearside_axle3`
        ,`effort_offside_axle3`
        ,`effort_single`
        ,`lock_nearside_axle1`
        ,`lock_offside_axle1`
        ,`lock_nearside_axle2`
        ,`lock_offside_axle2`
        ,`lock_nearside_axle3`
        ,`lock_offside_axle3`
        ,`lock_single`
        ,`imbalance_axle1`
        ,`imbalance_axle2`
        ,`imbalance_axle3`
        ,`imbalance_pass`
        ,`is_steered_axle1`
        ,`is_steered_axle2`
        ,`is_steered_axle3`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`effort_nearside_axle1`
        ,OLD.`effort_offside_axle1`
        ,OLD.`effort_nearside_axle2`
        ,OLD.`effort_offside_axle2`
        ,OLD.`effort_nearside_axle3`
        ,OLD.`effort_offside_axle3`
        ,OLD.`effort_single`
        ,OLD.`lock_nearside_axle1`
        ,OLD.`lock_offside_axle1`
        ,OLD.`lock_nearside_axle2`
        ,OLD.`lock_offside_axle2`
        ,OLD.`lock_nearside_axle3`
        ,OLD.`lock_offside_axle3`
        ,OLD.`lock_single`
        ,OLD.`imbalance_axle1`
        ,OLD.`imbalance_axle2`
        ,OLD.`imbalance_axle3`
        ,OLD.`imbalance_pass`
        ,OLD.`is_steered_axle1`
        ,OLD.`is_steered_axle2`
        ,OLD.`is_steered_axle3`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;

$$

DELIMITER ;

