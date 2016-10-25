SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

DROP TRIGGER `tr_security_card_drift_bd`;

DELIMITER ;;

CREATE TRIGGER `tr_security_card_drift_bd`
BEFORE DELETE ON `security_card_drift`
FOR EACH ROW
MainBlock: BEGIN
    -- tr_security_card_drift_bd Generated on 2016-10-20 11:55:04
    DECLARE c_version VARCHAR(256) DEFAULT 'tr_security_card_drift_bd Generated on 2016-10-20 11:55:04. $Id$';

    IF `is_mot_trigger_disabled`()
    THEN
        LEAVE MainBlock;
    END IF;

    IF (@app_user_id IS NULL)
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session variable @app_user_id cannot be NULL if triggers are enabled.';
    END IF;

    INSERT INTO `security_card_drift_hist`
        (`expired_on`
        ,`expired_by`
        ,`id`
        ,`security_card_id`
        ,`last_observed_drift`
        ,`created_by`
        ,`created_on`
        ,`last_updated_by`
        ,`last_updated_on`
        ,`version`)
        VALUES
        (CURRENT_TIMESTAMP(6)
        ,COALESCE(@app_user_id, 0)
        ,OLD.`id`
        ,OLD.`security_card_id`
        ,OLD.`last_observed_drift`
        ,OLD.`created_by`
        ,OLD.`created_on`
        ,OLD.`last_updated_by`
        ,OLD.`last_updated_on`
        ,OLD.`version`);
END;;
DELIMITER ;
-- END OF security_card_drift