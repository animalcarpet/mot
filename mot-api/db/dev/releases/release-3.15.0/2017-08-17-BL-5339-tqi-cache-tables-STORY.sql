DROP TABLE IF EXISTS tqi_rfr_count;

CREATE TABLE tqi_rfr_count
(
  `id`                     INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `period_start_date`      DATE NOT NULL,
  `site_id`                INTEGER(10) UNSIGNED,
  `organisation_id`        INTEGER(10) UNSIGNED,
  `person_id`              INTEGER(10) UNSIGNED NOT NULL,
  `vehicle_class_group_id` INTEGER(2) UNSIGNED,
  `test_item_category_id`  INTEGER(10) UNSIGNED NOT NULL,
  `failed_count`           INTEGER(10) UNSIGNED,
  `created_on`             DATETIME(6)          NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  KEY `ix_tqi_rfr_counts_site_id` (`site_id`),
  KEY `ix_tqi_rfr_counts_person_id` (`person_id`),
  KEY `ix_tqi_rfr_counts_org_site_person_id` (`organisation_id`, `site_id`, `person_id`)
);


DROP TABLE IF EXISTS tqi_test_count;

CREATE TABLE tqi_test_count
(
  `id`                                   INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `period_start_date`                    DATE NOT NULL,
  `site_id`                              INTEGER(10) UNSIGNED,
  `organisation_id`                      INTEGER(10) UNSIGNED,
  `person_id`                            INTEGER(10) NOT NULL,
  `vehicle_class_group_id`               INTEGER(2) NOT NULL,
  `total_time`                           INTEGER(10),
  `failed_count`                         INTEGER(10) UNSIGNED,
  `total_count`                          INTEGER(10) UNSIGNED,
  `vehicle_age_sum`                      BIGINT(18),
  `vehicles_with_manufacture_date_count` INTEGER(10) UNSIGNED,
  `created_on`                           DATETIME(6)          NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  KEY `ix_tqi_test_counts_site_id` (`site_id`),
  KEY `ix_tqi_test_counts_person_id` (`person_id`),
  KEY `ix_tqi_test_counts_org_site_person_id` (`organisation_id`, `site_id`, `person_id`)
);


