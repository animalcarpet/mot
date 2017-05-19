SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `weight_source_lookup` (`code`, `name`, `description`, `display_order`, `created_by`) VALUES
  ('ORD34', 'ord-misw', 'Other reliable data (provided by a tester) - Mass in Service Weight for class 3 or 4', 11, @app_user_id),
  ('ORD5', 'ord-dgw-mam', 'Other reliable data (provided by a tester) - Design Gross Weight for class 5', 12, @app_user_id),
  ('ORD7', 'ord-dgw', 'Other reliable data (provided by a tester) - Design Gross Weight for class 7', 13, @app_user_id),
  ('MAM', 'mam', 'Maximum Allowed Mass - Calculated', 14, @app_user_id);
