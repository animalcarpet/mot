SET @`app_user_id` = (SELECT `id`
                      FROM `person`
                      WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

ALTER TABLE `special_notice` ADD COLUMN `acknowledged_on` DATETIME DEFAULT NULL AFTER `is_acknowledged`;

ALTER TABLE `special_notice_hist` ADD COLUMN `acknowledged_on` DATETIME DEFAULT NULL AFTER `is_acknowledged`;

-- recreate special_notice triggers

-- before delete trigger
DROP TRIGGER IF EXISTS `tr_special_notice_bd`;

DELIMITER ;;
CREATE TRIGGER `tr_special_notice_bd`
BEFORE DELETE ON `special_notice`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_special_notice_bd Generated on 2016-12-29 14:42:34
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_special_notice_bd Generated on 2016-12-29 14:42:34. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `special_notice_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`username`
      ,`person_id`
      ,`special_notice_content_id`
      ,`is_acknowledged`
      ,`acknowledged_on`
      ,`is_deleted`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`username`
        ,OLD.`person_id`
        ,OLD.`special_notice_content_id`
        ,OLD.`is_acknowledged`
        ,OLD.`acknowledged_on`
        ,OLD.`is_deleted`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;


-- after update trigger
DROP TRIGGER IF EXISTS `tr_special_notice_au`;

DELIMITER ;;
CREATE TRIGGER `tr_special_notice_au`
AFTER UPDATE ON `special_notice`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_special_notice_au Generated on 2016-12-29 14:42:48
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_special_notice_au Generated on 2016-12-29 14:42:48. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `special_notice_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`username`
      ,`person_id`
      ,`special_notice_content_id`
      ,`is_acknowledged`
      ,`acknowledged_on`
      ,`is_deleted`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`username`
        ,OLD.`person_id`
        ,OLD.`special_notice_content_id`
        ,OLD.`is_acknowledged`
        ,OLD.`acknowledged_on`
        ,OLD.`is_deleted`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;