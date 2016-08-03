SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

SET @vehicle_class_1 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 1);
SET @vehicle_class_2 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 2);
SET @vehicle_class_3 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 3);
SET @vehicle_class_4 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 4);
SET @vehicle_class_5 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 5);
SET @vehicle_class_7 = (SELECT `id` FROM `vehicle_class` WHERE `code` = 7);

SET @brake_performance_not_tested_rfr_class_1_and_2 = (
  SELECT r.`id`
  FROM `reason_for_rejection` r
  WHERE r.`test_item_selector_name` = 'Performance > Brake performance'
  AND r.`inspection_manual_reference` = '3.3'
  AND r.`audience` = 't'
);

SET @brake_performance_not_tested_rfr_class_3_to_7 = (
  SELECT r.`id`
  FROM `reason_for_rejection` r
  WHERE r.`test_item_selector_name` = 'Brake performance > Brake performance'
  AND r.`inspection_manual_reference` = '3.7.A.1'
  AND r.`audience` = 't'
);

SET @headlamp_aim_not_tested_rfr_class_1_and_2 = (
  SELECT r.`id`
  FROM `reason_for_rejection` r
  WHERE r.`test_item_selector_name` = 'Motorcycle lighting and signalling > Headlamp aim'
  AND r.`inspection_manual_reference` = '1.6'
  AND r.`audience` = 't'
);

SET @headlamp_aim_not_tested_rfr_class_3_to_7 = (
  SELECT r.`id`
  FROM `reason_for_rejection` r
  WHERE r.`test_item_selector_name` = 'Lamps, Reflectors and Electrical Equipment > Headlamp aim'
  AND r.`inspection_manual_reference` = '1.8.A.1c'
  AND r.`audience` = 't'
);

-- add mappings for brake performance not tested RFR to vehicle classes 1, 2, 3, 4, 5 & 7
-- add mappings for headlamp aim not tested RFR to vehicle classes 1, 2, 3, 4, 5 & 7
INSERT INTO `rfr_vehicle_class_map`(`rfr_id`, `vehicle_class_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
(@brake_performance_not_tested_rfr_class_1_and_2, @vehicle_class_1, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@brake_performance_not_tested_rfr_class_1_and_2, @vehicle_class_2, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@brake_performance_not_tested_rfr_class_3_to_7, @vehicle_class_3, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@brake_performance_not_tested_rfr_class_3_to_7, @vehicle_class_4, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@brake_performance_not_tested_rfr_class_3_to_7, @vehicle_class_5, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@brake_performance_not_tested_rfr_class_3_to_7, @vehicle_class_7, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_1_and_2, @vehicle_class_1, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_1_and_2, @vehicle_class_2, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_3_to_7, @vehicle_class_3, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_3_to_7, @vehicle_class_4, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_3_to_7, @vehicle_class_5, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
(@headlamp_aim_not_tested_rfr_class_3_to_7, @vehicle_class_7, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));

-- add back mappings for brake test performance not tested test_item_category to vehicle classes 1 & 2
INSERT INTO `test_item_category_vehicle_class_map`(`test_item_category_id`, `vehicle_class_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES
  (10151, 1, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6)),
  (10151, 2, @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6));
