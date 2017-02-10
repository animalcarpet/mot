CREATE TABLE IF NOT EXISTS `model_technical_data_category` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(100) NOT NULL COMMENT 'Short description of model technical data category',
  `display_order`   SMALLINT UNSIGNED DEFAULT NULL COMMENT 'To allow VTD to be ordered by category in a prescribed manner', 
  `created_by`      INT UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_model_technical_data_category_created_by` (`created_by`),
  KEY `ix_model_technical_data_category_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_model_technical_data_category_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_category_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);


CREATE TABLE IF NOT EXISTS `model_technical_data_content` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `short_description` VARCHAR(50) NOT NULL COMMENT 'Short summary of model technical data description',    
  `description`     VARCHAR(4000) NOT NULL COMMENT 'Full vehicle technical information for tester', 
  `display_order`   SMALLINT UNSIGNED DEFAULT NULL, 
  `model_technical_data_category_id` BIGINT UNSIGNED NOT NULL,
  `created_by`      INT    UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_model_technical_data_content_created_by` (`created_by`),
  KEY `ix_model_technical_data_content_last_updated_by` (`last_updated_by`),
  KEY `ix_model_technical_data_content_model_technical_data_category_id` (`model_technical_data_category_id`),
  CONSTRAINT `fk_model_technical_data_content_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_content_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_content_model_technical_data_category_id` FOREIGN KEY (`model_technical_data_category_id`) REFERENCES `model_technical_data_category` (`id`)
);


CREATE TABLE IF NOT EXISTS `model_technical_data` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sub_model_name`  VARCHAR(100) DEFAULT NULL COMMENT 'Additional info to differentiate technical data within a model range',    
  `model_id`        INT UNSIGNED NOT NULL,
  `fuel_type_id`    SMALLINT DEFAULT NULL,
  `manufacture_start_date` DATETIME DEFAULT NULL,
  `manufacture_end_date`  DATETIME DEFAULT NULL,
  `created_by`      INT UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_model_technical_data_created_by` (`created_by`),
  KEY `ix_model_technical_data_last_updated_by` (`last_updated_by`),
  KEY `ix_model_technical_data_fuel_type_id` (`fuel_type_id`),
  KEY `ix_model_technical_data_model_id` (`model_id`),
  KEY `ix_model_technical_data_model_id_manufacture_date` (`model_id`,`manufacture_start_date`),
  CONSTRAINT `fk_model_technical_data_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_model_id_model_id` FOREIGN KEY (`model_id`) REFERENCES `model` (`id`),
  CONSTRAINT `fk_model_technical_data_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);


CREATE TABLE IF NOT EXISTS `model_technical_data_content_map` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,   
  `model_technical_data_id`    BIGINT UNSIGNED NOT NULL,
  `model_technical_data_content_id`  BIGINT UNSIGNED NOT NULL,
  `created_by`      INT UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_model_technical_data_content_map_created_by` (`created_by`),
  KEY `ix_model_technical_data_content_map_last_updated_by` (`last_updated_by`),
  KEY `ix_model_technical_data_content_map_model_technical_data_id` (`model_technical_data_id`),
  KEY `ix_model_technical_data_content_map_mtd_content_id` (`model_technical_data_content_id`),
  CONSTRAINT `fk_model_technical_data_content_map_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_content_map_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_model_technical_data_content_map_model_technical_data_id` FOREIGN KEY (`model_technical_data_id`) REFERENCES `model_technical_data` (`id`),
  CONSTRAINT `fk_model_technical_data_content_map_mtd_content_id` FOREIGN KEY (`model_technical_data_content_id`) REFERENCES `model_technical_data_content` (`id`)
);


CREATE TABLE IF NOT EXISTS `vehicle_model_technical_data_map` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,   
  `vehicle_id`      INT UNSIGNED NOT NULL,
  `model_technical_data_id`  BIGINT UNSIGNED NOT NULL,
  `created_by`      INT UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_vehicle_model_technical_data_map_created_by` (`created_by`),
  KEY `ix_vehicle_model_technical_data_map_last_updated_by` (`last_updated_by`),
  KEY `ix_vehicle_model_technical_data_map_vehicle_id` (`vehicle_id`),
  KEY `ix_vehicle_model_technical_data_map_model_technical_data_id` (`model_technical_data_id`),
  CONSTRAINT `fk_vehicle_model_technical_data_map_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_vehicle_model_technical_data_map_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_vehicle_model_technical_data_map_vehicle_id_vehicle_id` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`id`),
  CONSTRAINT `fk_vehicle_model_technical_data_map_model_technical_data_id` FOREIGN KEY (`model_technical_data_id`) REFERENCES `model_technical_data` (`id`)
);

DROP TRIGGER IF EXISTS `mot2`.`tr_vehicle_model_technical_data_map_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_vehicle_model_technical_data_map_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_category_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_category_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_content_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_content_bu`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_content_map_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_model_technical_data_content_map_bu`;

DELIMITER $$

CREATE TRIGGER `mot2`.`tr_vehicle_model_technical_data_map_bi`
BEFORE INSERT ON `mot2`.`vehicle_model_technical_data_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_vehicle_model_technical_data_map_bi Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_vehicle_model_technical_data_map_bi Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_vehicle_model_technical_data_map_bu`
BEFORE UPDATE ON `mot2`.`vehicle_model_technical_data_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_vehicle_model_technical_data_map_bu Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_vehicle_model_technical_data_map_bu Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_bi`
BEFORE INSERT ON `mot2`.`model_technical_data`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_bi Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_bi Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_bu`
BEFORE UPDATE ON `mot2`.`model_technical_data`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_bu Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_bu Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_category_bi`
BEFORE INSERT ON `mot2`.`model_technical_data_category`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_category_bi Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_category_bi Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_category_bu`
BEFORE UPDATE ON `mot2`.`model_technical_data_category`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_category_bu Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_category_bu Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_content_bi`
BEFORE INSERT ON `mot2`.`model_technical_data_content`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_content_bi Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_content_bi Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_content_bu`
BEFORE UPDATE ON `mot2`.`model_technical_data_content`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_content_bu Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_content_bu Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_content_map_bi`
BEFORE INSERT ON `mot2`.`model_technical_data_content_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_content_map_bi Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_content_map_bi Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

CREATE TRIGGER `mot2`.`tr_model_technical_data_content_map_bu`
BEFORE UPDATE ON `mot2`.`model_technical_data_content_map`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_model_technical_data_content_map_bu Generated on 2017-01-27 13:42:33
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_model_technical_data_content_map_bu Generated on 2017-01-27 13:42:33. $Id$';
    
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
END;

$$

DELIMITER ;



