ALTER TABLE `person_security_card_map` MODIFY COLUMN `valid_from` DATETIME;
ALTER TABLE `person_security_card_map_hist` MODIFY COLUMN `valid_from` DATETIME;

ALTER TABLE `person_security_card_map` MODIFY COLUMN `valid_to` DATETIME;
ALTER TABLE `person_security_card_map_hist` MODIFY COLUMN `valid_to` DATETIME;