SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

ALTER TABLE `notification`
  ADD `is_archived` TINYINT(4) NOT NULL  DEFAULT '0'
  AFTER `read_on`;

ALTER TABLE `notification_hist`
  ADD `is_archived` TINYINT(4) NOT NULL  DEFAULT '0'
  AFTER `read_on`;

DROP TRIGGER IF EXISTS tr_notification_bd;
DROP TRIGGER IF EXISTS tr_notification_au;

DELIMITER ;;

/**
  SET @db_name = 'mot2';
  SET @original_table = 'notification';
  SET @history_table = 'notification_hist';

  SELECT ddr_util.generate_table_trigger_ddl(@db_name, @original_table, @db_name, @history_table, 'tr_notification_bd', 'BD');
  SELECT ddr_util.generate_table_trigger_ddl(@db_name, @original_table, @db_name, @history_table, 'tr_notification_au', 'AU');
 */

CREATE TRIGGER `mot2`.`tr_notification_bd`
BEFORE DELETE ON `mot2`.`notification`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_notification_bd Generated on 2016-12-22 11:34:41
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_notification_bd Generated on 2016-12-22 11:34:41. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`notification_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`notification_template_id`
        ,`recipient_id`
        ,`read_on`
        ,`is_archived`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`notification_template_id`
        ,OLD.`recipient_id`
        ,OLD.`read_on`
        ,OLD.`is_archived`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;


CREATE TRIGGER `mot2`.`tr_notification_au`
AFTER UPDATE ON `mot2`.`notification`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_notification_au Generated on 2016-12-22 11:34:52
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_notification_au Generated on 2016-12-22 11:34:52. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`notification_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`notification_template_id`
        ,`recipient_id`
        ,`read_on`
        ,`is_archived`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`notification_template_id`
        ,OLD.`recipient_id`
        ,OLD.`read_on`
        ,OLD.`is_archived`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;