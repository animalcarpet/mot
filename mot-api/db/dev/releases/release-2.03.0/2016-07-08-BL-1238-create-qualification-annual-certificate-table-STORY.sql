# BL-1238
# new table
SET @app_user_id = (SELECT `id`
                    FROM `person`
                    WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

DROP TABLE IF EXISTS `qualification_annual_certificate`;
DROP TABLE IF EXISTS `qualification_annual_certificate_hist`;

CREATE TABLE `qualification_annual_certificate` (
  `id`                     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id`              INT(10) UNSIGNED NOT NULL,
  `vehicle_class_group_id` INT(10) UNSIGNED NOT NULL,
  `certificate_number`     VARCHAR(50)               DEFAULT NULL,
  `date_awarded`           DATE                      DEFAULT NULL,
  `score`                  INT              NOT NULL,
  `created_by`             INT(10) UNSIGNED NOT NULL,
  `created_on`             DATETIME(6)      NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by`        INT(10) UNSIGNED          DEFAULT NULL,
  `last_updated_on`        DATETIME(6)               DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP (6),
  `version`                INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_qualification_annual_certificate_person_id` (`person_id`),
  KEY `ix_qualification_annual_certificate_person_created_by` (`created_by`),
  KEY `ix_qualification_annual_certificate_person_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_qualification_annual_certificate_person_id_person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_qualification_annual_certificate_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_qualification_annual_certificate_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

CREATE TABLE `qualification_annual_certificate_hist` (
  `hist_id`                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `expired_on`             TIMESTAMP(6)        NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by`             INT(10) UNSIGNED             DEFAULT NULL,
  `id`                     INT(10) UNSIGNED             DEFAULT NULL,
  `person_id`              INT(10) UNSIGNED             DEFAULT NULL,
  `vehicle_class_group_id` INT(10) UNSIGNED             DEFAULT NULL,
  `certificate_number`     VARCHAR(50)                  DEFAULT NULL,
  `date_awarded`           DATE                         DEFAULT NULL,
  `score`                  INT                 NOT NULL,
  `created_by`             INT(10) UNSIGNED             DEFAULT NULL,
  `created_on`             DATETIME(6)                  DEFAULT NULL,
  `last_updated_by`        INT(10) UNSIGNED             DEFAULT NULL,
  `last_updated_on`        DATETIME(6)                  DEFAULT NULL,
  `version`                INT(10) UNSIGNED             DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `uq_qualification_annual_certificate` (`id`, `version`)
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

DELIMITER ;;
CREATE TRIGGER tr_qualification_annual_certificate_bi
BEFORE INSERT ON qualification_annual_certificate
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_qualification_annual_certificate_bi created manually on 2016-07-25. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;
    SET NEW.`version` = 1,
    NEW.`created_by` = COALESCE(@app_user_id, NEW.`last_updated_by`, NEW.`created_by`),
    NEW.`last_updated_by` = NEW.`created_by`,
    NEW.`created_on` = CURRENT_TIMESTAMP,
    NEW.`last_updated_on` = NEW.`created_on`;
  END ;;

DELIMITER ;;
CREATE TRIGGER tr_qualification_annual_certificate_bu
BEFORE UPDATE ON qualification_annual_certificate
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_qualification_annual_certificate_bu created manually on 2016-07-25. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    SET NEW.`version` = OLD.`version` + 1;
    SET NEW.`last_updated_by` = COALESCE(@app_user_id, NEW.`last_updated_by`);
    SET NEW.`last_updated_on` = CURRENT_TIMESTAMP;
  END ;;

DELIMITER ;;
CREATE TRIGGER tr_qualification_annual_certificate_au
AFTER UPDATE ON qualification_annual_certificate
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_qualification_annual_certificate_au created manually on 2016-07-25. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO qualification_annual_certificate_hist
    (`expired_on`
      , `expired_by`
      , `id`
      , `person_id`
      , `vehicle_class_group_id`
      , `certificate_number`
      , `date_awarded`
      , `score`
      , `created_by`
      , `created_on`
      , `last_updated_by`
      , `last_updated_on`
      , `version`)
    VALUES
      (NEW.`last_updated_on`
        , NEW.`last_updated_by`
        , OLD.`id`
        , OLD.`person_id`
        , OLD.`vehicle_class_group_id`
        , OLD.`certificate_number`
        , OLD.`date_awarded`
        , OLD.`score`
        , OLD.`created_by`
        , OLD.`created_on`
        , OLD.`last_updated_by`
        , OLD.`last_updated_on`
        , OLD.`version`);
  END ;;

DELIMITER ;;
CREATE TRIGGER tr_qualification_annual_certificate_bd
BEFORE DELETE ON qualification_annual_certificate
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'tr_qualification_annual_certificate_bd created manually on 2016-07-25. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO qualification_annual_certificate_hist
    (`expired_on`
      , `expired_by`
      , `id`
      , `person_id`
      , `vehicle_class_group_id`
      , `certificate_number`
      , `date_awarded`
      , `score`
      , `created_by`
      , `created_on`
      , `last_updated_by`
      , `last_updated_on`
      , `version`)
    VALUES
      (CURRENT_TIMESTAMP(6)
        , COALESCE(@app_user_id, 0)
        , OLD.`id`
        , OLD.`person_id`
        , OLD.`vehicle_class_group_id`
        , OLD.`certificate_number`
        , OLD.`date_awarded`
        , OLD.`score`
        , OLD.`created_by`
        , OLD.`created_on`
        , OLD.`last_updated_by`
        , OLD.`last_updated_on`
        , OLD.`version`);
  END;;