SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

INSERT INTO `security_question` (`question_text`, `question_group`, `display_order`, `created_by`, `last_updated_by`, `last_updated_on`)
VALUES ('What was the name of your first pet?', 1, 2, @app_user_id, @app_user_id, CURRENT_TIMESTAMP(6));
INSERT INTO `security_question` (`question_text`, `question_group`, `display_order`, `created_by`, `last_updated_by`, `last_updated_on`)
VALUES ('What was your favourite subject at school?', 2, 2, @app_user_id, @app_user_id, CURRENT_TIMESTAMP(6));
UPDATE security_question SET question_group=1, display_order=1 WHERE question_text='What did you want to be when you grew up?';
UPDATE security_question SET question_group=1, display_order=3 WHERE question_text='Who was your first kiss?';
UPDATE security_question SET question_group=1, display_order=4 WHERE question_text='Where did you go on your first memorable holiday?';
UPDATE security_question SET question_group=1, display_order=5 WHERE question_text='Who was your childhood hero?';
UPDATE security_question SET question_group=2, display_order=1 WHERE question_text='What was your favourite place to visit as a child?';
UPDATE security_question SET question_group=2, display_order=3 WHERE question_text='What was the name of your first stuffed animal, doll or action figure?';
UPDATE security_question SET question_group=2, display_order=4 WHERE question_text='Where did you go on your first school trip?';
UPDATE security_question SET question_group=2, display_order=5 WHERE question_text='What was the first concert you attended?';