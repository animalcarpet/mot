SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

SET @`englandId` = (
  SELECT `non_working_day_country_lookup`.`id`
  FROM `non_working_day_country_lookup`
    JOIN `country_lookup` ON `non_working_day_country_lookup`.`country_lookup_id` = `country_lookup`.`id`
  WHERE `country_lookup`.`code` = 'GBENG'
);

SET @`scotlandId` = (
  SELECT `non_working_day_country_lookup`.`id`
  FROM `non_working_day_country_lookup`
    JOIN `country_lookup` ON `non_working_day_country_lookup`.`country_lookup_id` = `country_lookup`.`id`
  WHERE `country_lookup`.`code` = 'GBSCT'
);

SET @`walesId` = (
  SELECT `non_working_day_country_lookup`.`id`
  FROM `non_working_day_country_lookup`
    JOIN `country_lookup` ON `non_working_day_country_lookup`.`country_lookup_id` = `country_lookup`.`id`
  WHERE `country_lookup`.`code` = 'GBWLS'
);

INSERT INTO `non_working_day_lookup` (
  `non_working_day_country_lookup_id`, `day`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
    VALUES
      (@`englandId`, '2017-01-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-04-14', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-04-17', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-05-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-05-29', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-08-28', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2017-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-01-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-03-30', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-04-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-05-07', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-05-28', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-08-27', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`englandId`, '2018-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),

      (@`scotlandId`, '2017-01-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-01-03', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-04-14', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-05-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-05-29', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-08-07', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-11-30', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2017-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-01-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-01-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-03-30', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-05-07', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-05-28', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-08-06', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-11-30', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`scotlandId`, '2018-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),

      (@`walesId`, '2017-01-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-04-14', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-04-17', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-05-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-05-29', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-08-28', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2017-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-01-01', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-03-30', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-04-02', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-05-07', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-05-28', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-08-27', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-12-25', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
      (@`walesId`, '2018-12-26', @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6))
;
