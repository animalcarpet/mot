SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `notification_template`
(`id`, `content`, `subject`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES (
36,
'<p>You can now view the latest test quality information for:</p> <strong>${siteName}</strong><br/>${siteRef}<br/>${address}<br/><br/> <p>This information will help you identify anomalies and improve MOT test quality at your site.</p> <a id="vts-tqi-monthly-notification-${id}" href="${url}">View test quality information</a>',
'New test quality information available',
@app_user_id,
CURRENT_TIMESTAMP (6),
@app_user_id,
CURRENT_TIMESTAMP (6)
);