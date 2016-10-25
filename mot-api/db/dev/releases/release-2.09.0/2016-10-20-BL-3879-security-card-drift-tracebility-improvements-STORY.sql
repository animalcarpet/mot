
SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');


CREATE TABLE `security_card_drift_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expired_on` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by` int(10) unsigned DEFAULT NULL,
  `id` int(10) unsigned DEFAULT NULL,
  `security_card_id` int unsigned NULL,
  `last_observed_drift` int NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_on` datetime(6) DEFAULT NULL,
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL,
  `version` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_security_card_drift_hist` (`id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='DDL GENERATED 2016-10-20 11:48:55';

DELIMITER ;;
CREATE TRIGGER `tr_security_card_drift_au`
AFTER UPDATE ON `security_card_drift`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_drift_au
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_drift_au Generated on 2016-10-20 11:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_drift_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`security_card_id`
        ,`last_observed_drift`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`security_card_id`
        ,OLD.`last_observed_drift`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_drift_bd`
BEFORE DELETE ON `security_card_drift`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_bd Generated on 2016-10-20 11:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_bd Generated on 2016-10-20 11:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`security_card_id`
        ,`last_observed_drift`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`security_card_id`
        ,OLD.`last_observed_drift`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- END OF security_card_drift