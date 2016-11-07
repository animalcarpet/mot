SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

SET @notificationId = (SELECT `id`
                       FROM `notification_template`
                       WHERE `content` = 'You have been nominated for the role of ${positionName} for ${siteName} (${siteOrOrganisationId}) by ${nominatorName}. Please confirm nomination.');

UPDATE `notification_template`
SET `content` = 'You have been assigned the role of ${positionName} for ${siteName} (${siteOrOrganisationId}) by ${nominatorName}. Accept or reject the new role.'
WHERE `id` = @notificationId;