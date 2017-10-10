CREATE TABLE IF NOT EXISTS `rfr_deficiency_category` (
  `id`              INT     UNSIGNED  NOT NULL    AUTO_INCREMENT,
  `code`            VARCHAR(2)        NOT NULL,
  `description`     VARCHAR(30)       NOT NULL,
  `created_by`      INT UNSIGNED      NOT NULL,
  `created_on`      DATETIME(6)       NOT NULL    DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED                  DEFAULT NULL,
  `last_updated_on` DATETIME(6)                   DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED      NOT NULL    DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_rfr_deficiency_category_created_by` (`created_by`),
  KEY `ix_rfr_deficiency_category_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_rfr_deficiency_category_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_rfr_deficiency_category_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);

DROP TRIGGER IF EXISTS `mot2`.`tr_rfr_deficiency_category_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_rfr_deficiency_category_bu`;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_rfr_deficiency_category_bi`
BEFORE INSERT ON `mot2`.`rfr_deficiency_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_rfr_deficiency_category_bi Generated on 2017-10-02 13:08:16
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_rfr_deficiency_category_bi Generated on 2017-10-02 13:08:16. $Id$';

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
CREATE TRIGGER `mot2`.`tr_rfr_deficiency_category_bu`
BEFORE UPDATE ON `mot2`.`rfr_deficiency_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_rfr_deficiency_category_bu Generated on 2017-10-02 13:10:03
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_rfr_deficiency_category_bu Generated on 2017-10-02 13:10:03. $Id$';

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
  END;;
DELIMITER ;

-- Add data to rfr_deficiency_category
SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
SET @app_user_id = (SELECT `id`
                      FROM `person`
                      WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `rfr_deficiency_category` (`id`,`code`,`description`,`created_by`) VALUES (0, 'PE', 'Pre-EU Directive',@app_user_id);
INSERT INTO `rfr_deficiency_category` (`id`,`code`,`description`,`created_by`) VALUES (1, 'D',  'Dangerous'       ,@app_user_id);
INSERT INTO `rfr_deficiency_category` (`id`,`code`,`description`,`created_by`) VALUES (2, 'MA', 'Major'           ,@app_user_id);
INSERT INTO `rfr_deficiency_category` (`id`,`code`,`description`,`created_by`) VALUES (3, 'MI', 'Minor'           ,@app_user_id);

ALTER TABLE `reason_for_rejection` ADD COLUMN
  `start_date`                  DATE           NOT NULL    DEFAULT '1900-01-01' AFTER `audience`;
ALTER TABLE `reason_for_rejection` ADD COLUMN
  `rfr_deficiency_category_id`  INT UNSIGNED               DEFAULT NULL AFTER `end_date`;

-- update history table
ALTER TABLE `reason_for_rejection_hist` ADD COLUMN
`start_date`                  DATE                          DEFAULT NULL AFTER `audience`;
ALTER TABLE `reason_for_rejection_hist` ADD COLUMN
`rfr_deficiency_category_id`  INT UNSIGNED                  DEFAULT NULL AFTER `end_date`;

-- regenerate triggers for reason_for_rejection
DROP TRIGGER IF EXISTS `mot2`.`tr_reason_for_rejection_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_reason_for_rejection_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_reason_for_rejection_bd`;
DROP TRIGGER IF EXISTS `mot2`.`tr_reason_for_rejection_au`;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_reason_for_rejection_bi`
BEFORE INSERT ON `mot2`.`reason_for_rejection`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_reason_for_rejection_bi Generated on 2017-10-03 10:00:10
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_reason_for_rejection_bi Generated on 2017-10-03 10:00:10. $Id$';

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
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_reason_for_rejection_bu`
BEFORE UPDATE ON `mot2`.`reason_for_rejection`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_reason_for_rejection_bu Generated on 2017-10-03 09:59:52
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_reason_for_rejection_bu Generated on 2017-10-03 09:59:52. $Id$';

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
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_reason_for_rejection_bd`
BEFORE DELETE ON `mot2`.`reason_for_rejection`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_reason_for_rejection_bd Generated on 2017-10-03 09:59:28
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_reason_for_rejection_bd Generated on 2017-10-03 09:59:28. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`reason_for_rejection_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`test_item_category_id`
      ,`test_item_selector_name`
      ,`test_item_selector_name_cy`
      ,`inspection_manual_reference`
      ,`minor_item`
      ,`location_marker`
      ,`qt_marker`
      ,`note`
      ,`manual`
      ,`spec_proc`
      ,`is_advisory`
      ,`is_prs_fail`
      ,`section_test_item_selector_id`
      ,`can_be_dangerous`
      ,`date_first_used`
      ,`audience`
      ,`start_date`
      ,`end_date`
      ,`rfr_deficiency_category_id`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`test_item_category_id`
        ,OLD.`test_item_selector_name`
        ,OLD.`test_item_selector_name_cy`
        ,OLD.`inspection_manual_reference`
        ,OLD.`minor_item`
        ,OLD.`location_marker`
        ,OLD.`qt_marker`
        ,OLD.`note`
        ,OLD.`manual`
        ,OLD.`spec_proc`
        ,OLD.`is_advisory`
        ,OLD.`is_prs_fail`
        ,OLD.`section_test_item_selector_id`
        ,OLD.`can_be_dangerous`
        ,OLD.`date_first_used`
        ,OLD.`audience`
        ,OLD.`start_date`
        ,OLD.`end_date`
        ,OLD.`rfr_deficiency_category_id`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_reason_for_rejection_au`
AFTER UPDATE ON `mot2`.`reason_for_rejection`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_reason_for_rejection_au Generated on 2017-10-03 09:58:48
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_reason_for_rejection_au Generated on 2017-10-03 09:58:48. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`reason_for_rejection_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`test_item_category_id`
      ,`test_item_selector_name`
      ,`test_item_selector_name_cy`
      ,`inspection_manual_reference`
      ,`minor_item`
      ,`location_marker`
      ,`qt_marker`
      ,`note`
      ,`manual`
      ,`spec_proc`
      ,`is_advisory`
      ,`is_prs_fail`
      ,`section_test_item_selector_id`
      ,`can_be_dangerous`
      ,`date_first_used`
      ,`audience`
      ,`start_date`
      ,`end_date`
      ,`rfr_deficiency_category_id`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`test_item_category_id`
        ,OLD.`test_item_selector_name`
        ,OLD.`test_item_selector_name_cy`
        ,OLD.`inspection_manual_reference`
        ,OLD.`minor_item`
        ,OLD.`location_marker`
        ,OLD.`qt_marker`
        ,OLD.`note`
        ,OLD.`manual`
        ,OLD.`spec_proc`
        ,OLD.`is_advisory`
        ,OLD.`is_prs_fail`
        ,OLD.`section_test_item_selector_id`
        ,OLD.`can_be_dangerous`
        ,OLD.`date_first_used`
        ,OLD.`audience`
        ,OLD.`start_date`
        ,OLD.`end_date`
        ,OLD.`rfr_deficiency_category_id`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;

-- All old RFRs are set to be "Pre-Eu Directive'
UPDATE `reason_for_rejection` set `rfr_deficiency_category_id` = 0;
-- set rfr_deficiency_category_id to not null + remove default
ALTER TABLE `reason_for_rejection` MODIFY COLUMN `rfr_deficiency_category_id` INT UNSIGNED NOT NULL;

-- Add FK
ALTER TABLE `reason_for_rejection` ADD KEY
  `ix_rfr_deficiency_category_id`(`rfr_deficiency_category_id`);
ALTER TABLE `reason_for_rejection` ADD CONSTRAINT
  `fk_reason_for_rejection_rfr_deficiency_category_id` FOREIGN KEY (`rfr_deficiency_category_id`) REFERENCES `rfr_deficiency_category`(`id`);


ALTER TABLE `test_item_category` ADD COLUMN
  `start_date`                  DATE       NOT NULL    DEFAULT '1900-01-01' AFTER `business_rule_id`;
ALTER TABLE `test_item_category` ADD COLUMN
  `end_date`                    DATE                   DEFAULT NULL AFTER `start_date`;

CREATE TABLE `test_item_category_hist` (
  `hist_id`                       BIGINT UNSIGNED     AUTO_INCREMENT,
  `expired_on`                    TIMESTAMP(6)        DEFAULT CURRENT_TIMESTAMP(6),
  `expired_by`                    INT    UNSIGNED     DEFAULT NULL,
  `id`                            INT    UNSIGNED     DEFAULT NULL,
  `parent_test_item_category_id`  INT    UNSIGNED     DEFAULT NULL,
  `section_test_item_category_id` INT    UNSIGNED     DEFAULT NULL,
  `business_rule_id`              INT    UNSIGNED     DEFAULT NULL,
  `start_date`                    DATE                DEFAULT NULL,
  `end_date`                      DATE                DEFAULT NULL,
  `created_by`                    INT UNSIGNED        DEFAULT NULL,
  `created_on`                    DATETIME(6)         DEFAULT NULL,
  `last_updated_by`               INT UNSIGNED        DEFAULT NULL,
  `last_updated_on`               DATETIME(6)         DEFAULT NULL,
  `version`                       INT UNSIGNED        DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  UNIQUE KEY `uq_test_item_category_hist` (`id`, `version`)
);

-- regenerate triggers for test_item_category
DROP TRIGGER IF EXISTS `mot2`.`tr_test_item_category_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_test_item_category_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_test_item_category_bd`;
DROP TRIGGER IF EXISTS `mot2`.`tr_test_item_category_au`;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_test_item_category_bi`
BEFORE INSERT ON `mot2`.`test_item_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_test_item_category_bi Generated on 2017-10-03 09:55:40
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_test_item_category_bi Generated on 2017-10-03 09:55:40. $Id$';

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
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_test_item_category_bu`
BEFORE UPDATE ON `mot2`.`test_item_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_test_item_category_bu Generated on 2017-10-03 09:55:21
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_test_item_category_bu Generated on 2017-10-03 09:55:21. $Id$';

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
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_test_item_category_bd`
BEFORE DELETE ON `mot2`.`test_item_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_test_item_category_bd Generated on 2017-10-03 09:54:55
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_test_item_category_bd Generated on 2017-10-03 09:54:55. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`test_item_category_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`parent_test_item_category_id`
      ,`section_test_item_category_id`
      ,`business_rule_id`
      ,`start_date`
      ,`end_date`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`parent_test_item_category_id`
        ,OLD.`section_test_item_category_id`
        ,OLD.`business_rule_id`
        ,OLD.`start_date`
        ,OLD.`end_date`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_test_item_category_au`
AFTER UPDATE ON `mot2`.`test_item_category`
FOR EACH ROW
    MainBlock: BEGIN
    -- tr_test_item_category_au Generated on 2017-10-03 09:54:15
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_test_item_category_au Generated on 2017-10-03 09:54:15. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`test_item_category_hist`
    (`expired_on`
      ,`expired_by`
      ,`id`
      ,`parent_test_item_category_id`
      ,`section_test_item_category_id`
      ,`business_rule_id`
      ,`start_date`
      ,`end_date`
      ,`created_by`
      ,`created_on`
      ,`last_updated_by`
      ,`last_updated_on`
      ,`version`)
    VALUES
      (NEW.`last_updated_on`
        ,NEW.`last_updated_by`
        ,OLD.`id`
        ,OLD.`parent_test_item_category_id`
        ,OLD.`section_test_item_category_id`
        ,OLD.`business_rule_id`
        ,OLD.`start_date`
        ,OLD.`end_date`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
  END;;
DELIMITER ;
