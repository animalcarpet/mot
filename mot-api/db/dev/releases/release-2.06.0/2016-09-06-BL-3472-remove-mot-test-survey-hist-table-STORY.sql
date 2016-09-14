SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

DROP TRIGGER IF EXISTS `tr_mot_test_survey_result_ai`;
DROP TRIGGER IF EXISTS `tr_mot_test_survey_result_au`;
DROP TRIGGER IF EXISTS `tr_mot_test_survey_result_ad`;

DROP TABLE IF EXISTS `mot_test_survey_hist`;
