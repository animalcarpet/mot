SET @card_secret = '-----BEGIN PGP MESSAGE-----
Version: BCPG v1.46

jA0EAwMCZIsq2TVkHiZgyUZvaApIr4zLJu52omoor/JIGIc211J3fm41+jYVvD6j
1AF2NAuEM16S3S2JwHunFVqD6vKwKsI4spYMrPK69yIMJmqc6rvR
=GW7j
-----END PGP MESSAGE-----';

SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data');

INSERT INTO `security_card` (`serial_number`, `secret`, `security_card_status_lookup_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`, `version`)
VALUES
	('STTA12345678', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057741', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057747', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057748', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057738', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057739', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
	,('STTA00057746', @card_secret,  (SELECT `id` FROM `security_card_status_lookup` WHERE `code` = 'UNASD'), @app_user_id, CURRENT_TIMESTAMP(6), @app_user_id, CURRENT_TIMESTAMP(6), 1)
