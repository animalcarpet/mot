SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

CREATE TABLE `mot_test_rfr_map_marked_as_repaired` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mot_test_rfr_map_id` bigint(20) unsigned NOT NULL UNIQUE,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mot_test_rfr_map_id` FOREIGN KEY (`mot_test_rfr_map_id`) REFERENCES `mot_test_rfr_map` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED COMMENT='Records the id of RFRs from a vehicle test that have been marked as repaired by the tester but have not yet been removed from the mot_test_rfr_map table';