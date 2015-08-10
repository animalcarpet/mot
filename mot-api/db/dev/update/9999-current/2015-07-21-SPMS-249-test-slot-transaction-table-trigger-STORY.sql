/*
DROP TRIGGER IF EXISTS `tr_test_slot_transaction_au`;

CREATE TRIGGER `tr_test_slot_transaction_au` AFTER UPDATE
ON `test_slot_transaction` FOR EACH ROW
INSERT INTO  `test_slot_transaction_hist` (`hist_transaction_type`, `hist_batch_number`,`id`,
`slots`,
`real_slots`,
`slots_after`,
`status_id`,
`payment_id`,
`state`,
`sales_reference`,
`unique_identifier`,
`organisation_id`,
`completed_on`,
`created`,
`created_by_username`,
`mot1_legacy_id`,
`created_by`,
`created_on`,
`last_updated_by`,
`last_updated_on`,
`version`,
`batch_number`)
VALUES ('U', COALESCE(@batch_number, CASE WHEN NEW.BATCH_NUMBER<> OLD.BATCH_NUMBER THEN NEW.BATCH_NUMBER ELSE 0 END), OLD.`id`,
OLD.`slots`,
OLD.`real_slots`,
OLD.`slots_after`,
OLD.`status_id`,
OLD.`payment_id`,
OLD.`state`,
OLD.`sales_reference`,
OLD.`unique_identifier`,
OLD.`organisation_id`,
OLD.`completed_on`,
OLD.`created`,
OLD.`created_by_username`,
OLD.`mot1_legacy_id`,
OLD.`created_by`,
OLD.`created_on`,
OLD.`last_updated_by`,
OLD.`last_updated_on`,
OLD.`version`,
OLD.`batch_number`);

DROP TRIGGER IF EXISTS `tr_test_slot_transaction_ad`;

CREATE TRIGGER `tr_test_slot_transaction_ad` AFTER DELETE ON `test_slot_transaction` FOR EACH ROW
INSERT INTO  `test_slot_transaction_hist` (`hist_transaction_type`, `hist_batch_number`, `id`,
`slots`,
`real_slots`,
`slots_after`,
`status_id`,
`payment_id`,
`state`,
`sales_reference`,
`unique_identifier`,
`organisation_id`,
`completed_on`,
`created`,
`created_by_username`,
`mot1_legacy_id`,
`created_by`,
`created_on`,
`last_updated_by`,
`last_updated_on`,
`version`,
`batch_number`)
VALUES ('D', COALESCE(@BATCH_NUMBER,0), OLD.`id`,
OLD.`slots`,
OLD.`real_slots`,
OLD.`slots_after`,
OLD.`status_id`,
OLD.`payment_id`,
OLD.`state`,
OLD.`sales_reference`,
OLD.`unique_identifier`,
OLD.`organisation_id`,
OLD.`completed_on`,
OLD.`created`,
OLD.`created_by_username`,
OLD.`mot1_legacy_id`,
OLD.`created_by`,
OLD.`created_on`,
OLD.`last_updated_by`,
OLD.`last_updated_on`,
OLD.`version`,
OLD.`batch_number`);*/