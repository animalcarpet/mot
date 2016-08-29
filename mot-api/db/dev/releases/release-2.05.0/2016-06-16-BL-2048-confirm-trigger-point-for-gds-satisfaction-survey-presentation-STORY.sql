DROP TABLE IF EXISTS `mot_test_survey_result_hist`;
DROP TABLE IF EXISTS `mot_test_survey_result`;
DROP TABLE IF EXISTS `survey_result_hist`;
DROP TABLE IF EXISTS `survey_result`;

SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

CREATE TABLE IF NOT EXISTS `survey` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `satisfaction_rating` TINYINT DEFAULT NULL,
  `created_on`          DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_on`     DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`             INT unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `mot_test_survey` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mot_test_id`     BIGINT UNSIGNED NOT NULL,
  `token`           CHAR(36) DEFAULT NULL,
  `survey_id`       BIGINT UNSIGNED DEFAULT NULL,
  `created_by`      INT(10) UNSIGNED NOT NULL,
  `created_on`      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT(10) UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version`         INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_mot_test_survey_person_created_by` (`created_by`),
  KEY `ix_mot_test_survey_person_last_updated_by` (`last_updated_by`),
  CONSTRAINT `fk_mot_test_id_mot_test_id` FOREIGN KEY (`mot_test_id`) REFERENCES `mot_test` (`id`),
  CONSTRAINT `fk_survey_id_survey_id` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`id`),
  CONSTRAINT `fk_mot_test_survey_created_by_person_id` FOREIGN KEY (`created_by`) REFERENCES `person` (`id`),
  CONSTRAINT `fk_mot_test_survey_last_updated_by_person_id` FOREIGN KEY (`last_updated_by`) REFERENCES `person` (`id`)
);

CREATE TABLE IF NOT EXISTS `mot_test_survey_hist` (
  `hist_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hist_timestamp` TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `hist_transaction_type` CHAR NOT NULL DEFAULT 'U',
  `hist_batch_number` INT unsigned NOT NULL DEFAULT '0',
  `id` BIGINT UNSIGNED NOT NULL,
  `mot_test_id` BIGINT UNSIGNED NOT NULL,
  `token` CHAR(36) DEFAULT NULL,
  `survey_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_by` INT(10) UNSIGNED NOT NULL,
  `created_on` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `last_updated_by` INT(10) UNSIGNED DEFAULT NULL,
  `last_updated_on` DATETIME(6) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
  `version` INT unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`hist_id`)
);

DROP TRIGGER IF EXISTS `tr_mot_test_survey_bi`;
DELIMITER $$
CREATE TRIGGER `tr_mot_test_survey_result_bi` BEFORE INSERT
ON `mot_test_survey` FOR EACH ROW  BEGIN
  SET
  NEW.`version` = 1,
  NEW.`created_by` = IF(@app_user_id IS NOT NULL, @app_user_id, NEW.`created_by`),
  NEW.`last_updated_by` = IF(@app_user_id IS NOT NULL, @app_user_id, NEW.`last_updated_by`);
END;
$$
DELIMITER ;



DROP TRIGGER IF EXISTS `tr_mot_test_survey_ai`;
DELIMITER $$
CREATE TRIGGER `tr_mot_test_survey_result_ai` AFTER INSERT
ON `mot_test_survey`
FOR EACH ROW BEGIN
  INSERT INTO  `mot_test_survey_hist` (
    `hist_transaction_type`,
    `id`,
    `mot_test_id`,
    `token`,
    `survey_id`,
    `created_by`,
    `created_on`,
    `last_updated_by`,
    `last_updated_on`,
    `version`
  )
  VALUES
    (
      'I',
      NEW.`id`,
      NEW.`mot_test_id`,
      NEW.`token`,
      NEW.`survey_id`,
      NEW.`created_by`,
      NEW.`created_on`,
      NEW.`last_updated_by`,
      NEW.`last_updated_on`,
      NEW.`version`
    );
END;
$$
DELIMITER ;



DROP TRIGGER IF EXISTS `tr_mot_test_survey_bu`;
DELIMITER $$
CREATE TRIGGER `tr_mot_test_survey_result_bu` BEFORE UPDATE
ON `mot_test_survey` FOR EACH ROW  BEGIN
  SET
  NEW.`version` = OLD.`version` + 1,
  NEW.`last_updated_by` = IF(@app_user_id IS NOT NULL, @app_user_id, NEW.`last_updated_by`);
END;
$$
DELIMITER ;



DROP TRIGGER IF EXISTS `tr_mot_test_survey_au`;
DELIMITER $$
CREATE TRIGGER `tr_mot_test_survey_result_au` AFTER UPDATE
ON `mot_test_survey`
FOR EACH ROW
  INSERT INTO `mot_test_survey_hist`
  (
    `hist_transaction_type`,
    `id`,
    `mot_test_id`,
    `token`,
    `survey_id`,
    `created_by`,
    `created_on`,
    `last_updated_by`,
    `last_updated_on`,
    `version`
  )
  VALUES
    (
      'U',
      NEW.`id`,
      NEW.`mot_test_id`,
      NEW.`token`,
      NEW.`survey_id`,
      NEW.`created_by`,
      NEW.`created_on`,
      NEW.`last_updated_by`,
      NEW.`last_updated_on`,
      NEW.`version`
    );
$$
DELIMITER ;



DROP TRIGGER IF EXISTS `tr_mot_test_survey_ad`;
DELIMITER $$
CREATE TRIGGER `tr_mot_test_survey_result_ad` AFTER DELETE
ON `mot_test_survey`
FOR EACH ROW
  INSERT INTO `mot_test_survey_hist`
  (
    `hist_transaction_type`,
    `id`,
    `mot_test_id`,
    `token`,
    `survey_id`,
    `created_by`,
    `created_on`,
    `last_updated_by`,
    `last_updated_on`,
    `version`
  )
  VALUES
    (
      'D',
      OLD.`id`,
      OLD.`mot_test_id`,
      OLD.`token`,
      OLD.`survey_id`,
      OLD.`created_by`,
      OLD.`created_on`,
      OLD.`last_updated_by`,
      OLD.`last_updated_on`,
      OLD.`version`
    );
$$
DELIMITER ;
