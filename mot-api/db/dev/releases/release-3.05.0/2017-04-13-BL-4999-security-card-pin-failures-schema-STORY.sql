CREATE TABLE IF NOT EXISTS `security_card_pin_failures` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id`       INT UNSIGNED NOT NULL COMMENT 'Person who has failed entering their pin',
  `failure_count`   TINYINT NOT NULL COMMENT 'Times the person has failed entering their pin in this failure window',
  `failure_time`    DATETIME(6) NOT NULL COMMENT 'Time of the fist failure in the failure window',
  `created_by`      INT UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_security_card_pin_failures_person_id` (`person_id`),
  KEY `ix_security_card_pin_failures_created_by` (`created_by`),
  KEY `ix_security_card_pin_failures_last_updated_by` (`last_updated_by`),
  KEY `ix_security_card_pin_failures_person_id` (`person_id`),
  CONSTRAINT `fk_security_card_pin_failures_person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_pin_failures_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_security_card_pin_failures_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);

CREATE TABLE IF NOT EXISTS `security_card_pin_failures_hist` (
  `hist_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` BIGINT UNSIGNED DEFAULT NULL,
  `person_id`       INT UNSIGNED DEFAULT NULL,
  `failure_count`   TINYINT DEFAULT NULL,
  `failure_time`    DATETIME(6) DEFAULT NULL,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_on`      DATETIME(6) DEFAULT NULL,
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL,
  `version`         INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  UNIQUE KEY `security_card_pin_failures_hist` (`id`,`version`)
);

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_security_card_pin_failures_au`
AFTER UPDATE ON `mot2`.`security_card_pin_failures`
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_pin_failures_au Generated on 2017-04-17 09:42:33. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`security_card_pin_failures_hist`
    (
      `id`,
      `person_id`,
      `failure_count`,
      `failure_time`,
      `created_by`,
      `created_on`,
      `last_updated_by`,
      `last_updated_on`,
      `version`
    )
    VALUES
    (
      OLD.`id`,
      OLD.`person_id`,
      OLD.`failure_count`,
      OLD.`failure_time`,
      OLD.`created_by`,
      OLD.`created_on`,
      OLD.`last_updated_by`,
      OLD.`last_updated_on`,
      OLD.`version`
    );
  END ;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_security_card_pin_failures_bi`
BEFORE INSERT ON `mot2`.`security_card_pin_failures`
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_pin_failures_bi Generated on 2017-04-17 09:42:33. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
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
  END ;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_security_card_pin_failures_bu`
BEFORE UPDATE ON `mot2`.`security_card_pin_failures`
FOR EACH ROW
    MainBlock: BEGIN
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_pin_failures_bu Generated on 2017-04-17 09:42:33. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
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
  END ;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_security_card_pin_failures_bd`
BEFORE DELETE ON `mot2`.`security_card_pin_failures`
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_pin_failures_bd Generated on 2017-04-17 09:42:33. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`security_card_pin_failures_hist`
    (
      `id`,
      `person_id`,
      `failure_count`,
      `failure_time`,
      `created_by`,
      `created_on`,
      `last_updated_by`,
      `last_updated_on`,
      `version`
    )
    VALUES
    (
      OLD.`id`,
      OLD.`person_id`,
      OLD.`failure_count`,
      OLD.`failure_time`,
      OLD.`created_by`,
      OLD.`created_on`,
      OLD.`last_updated_by`,
      OLD.`last_updated_on`,
      OLD.`version`
    );
  END ;;
DELIMITER ;
