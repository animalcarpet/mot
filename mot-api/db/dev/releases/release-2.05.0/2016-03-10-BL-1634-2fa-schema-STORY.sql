-- triggers
SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data');

CREATE TABLE `security_card_status_lookup` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(30) NOT NULL,
  `created_by` int unsigned NOT NULL,
  `created_on` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_card_status_lookup_code` (`code`),
  KEY `ix_security_card_status_lookup_created_by` (`created_by`),
  KEY `ix_security_card_status_lookup_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_security_card_status_lookup_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_status_lookup_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `security_card_status_lookup_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expired_on` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by` int(10) unsigned DEFAULT NULL,
  `id` tinyint(3) unsigned DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_on` datetime(6) DEFAULT NULL,
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL,
  `version` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_security_card_status_lookup_hist` (`id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='DDL GENERATED 2016-08-03 14:45:54';

DELIMITER ;;
CREATE TRIGGER `tr_security_card_status_lookup_bi`
BEFORE INSERT ON `security_card_status_lookup`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_status_lookup_bi Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_status_lookup_bi Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = 1;
    SET NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`);
    SET NEW.`last_updated_by` = NEW.`created_by`;
    SET NEW.`created_on` = CURRENT_TIMESTAMP;
    SET NEW.`last_updated_on` = NEW.`created_on`;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_status_lookup_bu`
BEFORE UPDATE ON `security_card_status_lookup`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_status_lookup_bu Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_status_lookup_bu Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_status_lookup_au`
AFTER UPDATE ON `security_card_status_lookup`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_status_lookup_au Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_status_lookup_au Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_status_lookup_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`code`
        ,`name`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`code`
        ,OLD.`name`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_status_lookup_bd`
BEFORE DELETE ON `security_card_status_lookup`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_status_lookup_bd Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_status_lookup_bd Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_status_lookup_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`code`
        ,`name`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`code`
        ,OLD.`name`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- security_card_status_lookup

CREATE TABLE `security_card` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(20) NOT NULL,
  `secret` varchar(250) NOT NULL,
  `security_card_status_lookup_id` tinyint unsigned NOT NULL,
  `created_by` int unsigned NOT NULL,
  `created_on` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_card_serial_number` (`serial_number`),
  KEY `ix_security_card_security_card_status_lookup_id` (`security_card_status_lookup_id`),
  KEY `ix_security_card_person_created_by` (`created_by`),
  KEY `ix_security_card_person_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_security_card_status_id_security_card_status_lookup_id` FOREIGN KEY (`security_card_status_lookup_id`) REFERENCES `security_card_status_lookup` (`id`),
  CONSTRAINT `fk_security_card_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `security_card_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expired_on` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by` int(10) unsigned DEFAULT NULL,
  `id` int(10) unsigned DEFAULT NULL,
  `serial_number` varchar(20) DEFAULT NULL,
  `secret` varchar(250) DEFAULT NULL,
  `security_card_status_lookup_id` tinyint(3) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_on` datetime(6) DEFAULT NULL,
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL,
  `version` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_security_card_hist` (`id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='DDL GENERATED 2016-08-03 14:48:55';

DELIMITER ;;
CREATE TRIGGER `tr_security_card_bi`
BEFORE INSERT ON `security_card`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_bi Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_bi Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = 1;
    SET NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`);
    SET NEW.`last_updated_by` = NEW.`created_by`;
    SET NEW.`created_on` = CURRENT_TIMESTAMP;
    SET NEW.`last_updated_on` = NEW.`created_on`;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_bu`
BEFORE UPDATE ON `security_card`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_bu Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_bu Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_au`
AFTER UPDATE ON `security_card`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_au Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_au Generated on 2016-08-03 14:55:04. $Id$';

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
        ,`serial_number`
        ,`secret`
        ,`security_card_status_lookup_id`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`serial_number`
        ,OLD.`secret`
        ,OLD.`security_card_status_lookup_id`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_bd`
BEFORE DELETE ON `security_card`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_bd Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_bd Generated on 2016-08-03 14:55:04. $Id$';

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
        ,`serial_number`
        ,`secret`
        ,`security_card_status_lookup_id`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`serial_number`
        ,OLD.`secret`
        ,OLD.`security_card_status_lookup_id`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- END OF security_card


CREATE TABLE `security_card_drift` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `security_card_id` int unsigned NOT NULL,
  `last_observed_drift` int NOT NULL COMMENT 'in seconds, negative - past current time, positive - ahead of current time',
  `created_by` int unsigned NOT NULL,
  `created_on` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_card_drift_security_card_id` (`security_card_id`),
  KEY `ix_security_card_drift_person_created_by` (`created_by`),
  KEY `ix_security_card_drift_person_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_security_card_drift_security_card_id_security_card_id` FOREIGN KEY (`security_card_id`) REFERENCES `security_card` (`id`),
  CONSTRAINT `fk_security_card_drift_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_drift_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_drift_bi`
BEFORE INSERT ON `security_card_drift`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_bi Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_drift_bi Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = 1;
    SET NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`);
    SET NEW.`last_updated_by` = NEW.`created_by`;
    SET NEW.`created_on` = CURRENT_TIMESTAMP;
    SET NEW.`last_updated_on` = NEW.`created_on`;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_drift_bu`
BEFORE UPDATE ON `security_card_drift`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_bu Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_drift_bu Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
END;;
DELIMITER ;
-- END OF security_card_drift


CREATE TABLE `person_security_card_map` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int unsigned NOT NULL,
  `security_card_id` int unsigned NOT NULL,
  `valid_from` DATE  NOT NULL,
  `valid_to` DATE DEFAULT NULL,
  `created_by` int unsigned NOT NULL,
  `created_on` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_pscm_person_id_valid_from` (`person_id`, `valid_from`),
  KEY `ix_pscm_security_card_id` (`security_card_id`),
  KEY `ix_pscm_person_created_by` (`created_by`),
  KEY `ix_pscm_person_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_pscm_person_id_person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_pscm_security_card_id_security_card_id` FOREIGN KEY (`security_card_id`) REFERENCES `security_card` (`id`),
  CONSTRAINT `fk_pscm_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_pscm_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `person_security_card_map_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expired_on` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by` int(10) unsigned DEFAULT NULL,
  `id` int(10) unsigned DEFAULT NULL,
  `person_id` int(10) unsigned DEFAULT NULL,
  `security_card_id` int(10) unsigned DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_on` datetime(6) DEFAULT NULL,
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL,
  `version` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_person_security_card_map_hist` (`id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='DDL GENERATED 2016-08-03 14:49:06';

DELIMITER ;;
CREATE TRIGGER `tr_person_security_card_map_bi`
BEFORE INSERT ON `person_security_card_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_person_security_card_map_bi Generated on 2016-08-03 14:55:03
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_person_security_card_map_bi Generated on 2016-08-03 14:55:03. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = 1;
    SET NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`);
    SET NEW.`last_updated_by` = NEW.`created_by`;
    SET NEW.`created_on` = CURRENT_TIMESTAMP;
    SET NEW.`last_updated_on` = NEW.`created_on`;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_person_security_card_map_bu`
BEFORE UPDATE ON `person_security_card_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_person_security_card_map_bu Generated on 2016-08-03 14:55:03
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_person_security_card_map_bu Generated on 2016-08-03 14:55:03. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_person_security_card_map_au`
AFTER UPDATE ON `person_security_card_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_person_security_card_map_au Generated on 2016-08-03 14:55:03
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_person_security_card_map_au Generated on 2016-08-03 14:55:03. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `person_security_card_map_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`person_id`
        ,`security_card_id`
        ,`valid_from`
        ,`valid_to`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`person_id`
        ,OLD.`security_card_id`
        ,OLD.`valid_from`
        ,OLD.`valid_to`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_person_security_card_map_bd`
BEFORE DELETE ON `person_security_card_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_person_security_card_map_bd Generated on 2016-08-03 14:55:03
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_person_security_card_map_bd Generated on 2016-08-03 14:55:03. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `person_security_card_map_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`person_id`
        ,`security_card_id`
        ,`valid_from`
        ,`valid_to`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`person_id`
        ,OLD.`security_card_id`
        ,OLD.`valid_from`
        ,OLD.`valid_to`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- END OF person_security_card_map

CREATE TABLE `security_card_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int unsigned NOT NULL,
  `vts_name` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(50) NOT NULL,
  `address_line_2` varchar(50) DEFAULT NULL,
  `address_line_3` varchar(50) DEFAULT NULL,
  `postcode` varchar(10)  NOT NULL,
  `town` varchar(50) NOT NULL,
  `submitted_on` datetime(6) NOT NULL,
  `submitted_by` int unsigned NOT NULL,
  `created_by` int unsigned NOT NULL,
  `created_on` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_security_card_order_person_id` (`person_id`),
  KEY `ix_security_card_order_created_by` (`created_by`),
  KEY `ix_security_card_order_last_updated_by` (`last_updated_by`),
  KEY `ix_security_card_order_submitted_on` (`submitted_on`),
  KEY `ix_security_card_order_submitted_by` (`submitted_by`),
  CONSTRAINT `fk_security_card_order_person_id_person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_order_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_order_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_order_submitted_by_person_id` FOREIGN KEY (`submitted_by`) REFERENCES `person` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `security_card_order_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expired_on` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by` int(10) unsigned DEFAULT NULL,
  `id` int(10) unsigned DEFAULT NULL,
  `person_id` int(10) unsigned DEFAULT NULL,
  `vts_name` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(50) DEFAULT NULL,
  `address_line_2` varchar(50) DEFAULT NULL,
  `address_line_3` varchar(50) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(50) DEFAULT NULL,
  `submitted_on` datetime(6) DEFAULT NULL,
  `submitted_by` int unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_on` datetime(6) DEFAULT NULL,
  `last_updated_by` int(10) unsigned DEFAULT NULL,
  `last_updated_on` datetime(6) DEFAULT NULL,
  `version` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_security_card_order_hist` (`id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='DDL GENERATED 2016-08-03 14:55:00';

DELIMITER ;;
CREATE TRIGGER `tr_security_card_order_bi`
BEFORE INSERT ON `security_card_order`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_bi Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_order_bi Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = 1;
    SET NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`);
    SET NEW.`last_updated_by` = NEW.`created_by`;
    SET NEW.`created_on` = CURRENT_TIMESTAMP;
    SET NEW.`last_updated_on` = NEW.`created_on`;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_order_bu`
BEFORE UPDATE ON `security_card_order`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_bu Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_order_bu Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_order_au`
AFTER UPDATE ON `security_card_order`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_au Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_order_au Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_order_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`person_id`
        ,`vts_name`
        ,`address_line_1`
        ,`address_line_2`
        ,`address_line_3`
        ,`postcode`
        ,`town`
        ,`submitted_on`
        ,`submitted_by`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`person_id`
        ,OLD.`vts_name`
        ,OLD.`address_line_1`
        ,OLD.`address_line_2`
        ,OLD.`address_line_3`
        ,OLD.`postcode`
        ,OLD.`town`
        ,OLD.`submitted_on`
        ,OLD.`submitted_by`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `tr_security_card_order_bd`
BEFORE DELETE ON `security_card_order`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_order_bd Generated on 2016-08-03 14:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_order_bd Generated on 2016-08-03 14:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_order_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`person_id`
        ,`vts_name`
        ,`address_line_1`
        ,`address_line_2`
        ,`address_line_3`
        ,`postcode`
        ,`town`
        ,`submitted_on`
        ,`submitted_by`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`person_id`
        ,OLD.`vts_name`
        ,OLD.`address_line_1`
        ,OLD.`address_line_2`
        ,OLD.`address_line_3`
        ,OLD.`postcode`
        ,OLD.`town`
        ,OLD.`submitted_on`
        ,OLD.`submitted_by`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- END OF security_card_order
