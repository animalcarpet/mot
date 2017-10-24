CREATE TABLE `smmt_make_map` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `smmt_make` varchar(50) DEFAULT NULL,
  `make` varchar(50) DEFAULT NULL,
  `created_by`      INT UNSIGNED      NOT NULL,
  `created_on`      DATETIME(6)       NOT NULL    DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT UNSIGNED                  DEFAULT NULL,
  `last_updated_on` DATETIME(6)                   DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED      NOT NULL    DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_smmt_make_map_created_by` (`created_by`),
  KEY `ix_smmt_make_map_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_smmt_make_map_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_smmt_make_map_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);

DROP TRIGGER IF EXISTS `mot2`.`tr_smmt_make_map_bi`;
DROP TRIGGER IF EXISTS `mot2`.`tr_smmt_make_map_bu`;

DELIMITER ;;
CREATE TRIGGER `mot2`.`tr_smmt_make_map_bi`
BEFORE INSERT ON `mot2`.`smmt_make_map`
FOR EACH ROW
    MainBlock: BEGIN
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_smmt_make_map_bi Generated on 2017-10-02 13:08:16. $Id$';

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
CREATE TRIGGER `mot2`.`tr_smmt_make_map_bu`
BEFORE UPDATE ON `mot2`.`smmt_make_map`
FOR EACH ROW
    MainBlock: BEGIN
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_smmt_make_map_bu Generated on 2017-10-02 13:10:03. $Id$';

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

CREATE TABLE `smmt_make_map_hist` (
  `hist_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` int(20) unsigned DEFAULT NULL,
  `smmt_make` varchar(50) DEFAULT NULL,
  `make` varchar(50) DEFAULT NULL,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_on`      DATETIME(6) DEFAULT NULL,
  `last_updated_by` INT UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL,
  `version`         INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  UNIQUE KEY `smmt_make_map_hist` (`id`,`version`)
);

DELIMITER ;;
CREATE TRIGGER `mot2`.`smmt_make_map_au`
AFTER UPDATE ON `mot2`.`smmt_make_map`
FOR EACH ROW
    MainBlock: BEGIN

    DECLARE c_version VARCHAR(256) DEFAULT 'smmt_make_map_au Generated on 2017-04-17 09:42:33. $Id$';

    IF `mot2`.`is_mot_trigger_disabled`()
    THEN
      LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `mot2`.`smmt_make_map_hist`
    (
      `id`,
      `smmt_make`,
      `make`,
      `created_by`,
      `created_on`,
      `last_updated_by`,
      `last_updated_on`,
      `version`
    )
    VALUES
    (
      OLD.`id`,
      OLD.`smmt_make`,
      OLD.`make`,
      OLD.`created_by`,
      OLD.`created_on`,
      OLD.`last_updated_by`,
      OLD.`last_updated_on`,
      OLD.`version`
    );
  END ;;
DELIMITER ;

SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
SET @app_user_id = (SELECT `id`
                      FROM `person`
                      WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO smmt_make_map(smmt_make, make) values ('ABARTH', 'ABARTH');
INSERT INTO smmt_make_map(smmt_make, make) values ('ALFA ROMEO', 'ALFA ROMEO');
INSERT INTO smmt_make_map(smmt_make, make) values ('AUDI', 'AUDI');
INSERT INTO smmt_make_map(smmt_make, make) values ('BMW', 'BMW');
INSERT INTO smmt_make_map(smmt_make, make) values ('CHEVROLET', 'CHEVROLET');
INSERT INTO smmt_make_map(smmt_make, make) values ('CHRYSLER', 'CHRYSLER');
INSERT INTO smmt_make_map(smmt_make, make) values ('CHRYSLER', 'CHRYSLER JEEP');
INSERT INTO smmt_make_map(smmt_make, make) values ('CITROEN', 'CITROEN');
INSERT INTO smmt_make_map(smmt_make, make) values ('DACIA', 'DACIA');
INSERT INTO smmt_make_map(smmt_make, make) values ('DODGE', 'DODGE');
INSERT INTO smmt_make_map(smmt_make, make) values ('FIAT', 'FIAT');
INSERT INTO smmt_make_map(smmt_make, make) values ('FORD', 'FORD');
INSERT INTO smmt_make_map(smmt_make, make) values ('HONDA', 'HONDA');
INSERT INTO smmt_make_map(smmt_make, make) values ('HYUNDAI', 'HYUNDAI');
INSERT INTO smmt_make_map(smmt_make, make) values ('INFINITI', 'INFINITI');
INSERT INTO smmt_make_map(smmt_make, make) values ('JAGUAR', 'JAGUAR');
INSERT INTO smmt_make_map(smmt_make, make) values ('JEEP', 'JEEP');
INSERT INTO smmt_make_map(smmt_make, make) values ('KIA', 'KIA');
INSERT INTO smmt_make_map(smmt_make, make) values ('LANCIA', 'LANCIA');
INSERT INTO smmt_make_map(smmt_make, make) values ('LAND ROVER', 'LAND ROVER');
INSERT INTO smmt_make_map(smmt_make, make) values ('LEXUS', 'LEXUS');
INSERT INTO smmt_make_map(smmt_make, make) values ('MAZDA', 'MAZDA');
INSERT INTO smmt_make_map(smmt_make, make) values ('MINI', 'MINI');
INSERT INTO smmt_make_map(smmt_make, make) values ('MITSUBISHI', 'MITSUBISHI');
INSERT INTO smmt_make_map(smmt_make, make) values ('MITSUBISHI', 'MITSUBISHI FUSO');
INSERT INTO smmt_make_map(smmt_make, make) values ('NISSAN', 'NISSAN');
INSERT INTO smmt_make_map(smmt_make, make) values ('OPEL', 'OPEL');
INSERT INTO smmt_make_map(smmt_make, make) values ('PEUGEOT', 'PEUGEOT');
INSERT INTO smmt_make_map(smmt_make, make) values ('PORSCHE', 'PORSCHE');
INSERT INTO smmt_make_map(smmt_make, make) values ('RENAULT', 'RENAULT');
INSERT INTO smmt_make_map(smmt_make, make) values ('SEAT', 'SEAT');
INSERT INTO smmt_make_map(smmt_make, make) values ('SKODA', 'SKODA');
INSERT INTO smmt_make_map(smmt_make, make) values ('SUZUKI', 'SUZUKI');
INSERT INTO smmt_make_map(smmt_make, make) values ('TOYOTA', 'TOYOTA');
INSERT INTO smmt_make_map(smmt_make, make) values ('VAUXHALL', 'VAUXHALL');
INSERT INTO smmt_make_map(smmt_make, make) values ('VOLKSWAGEN', 'VOLKSWAGEN');
INSERT INTO smmt_make_map(smmt_make, make) values ('VOLVO', 'VOLVO');