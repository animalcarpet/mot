SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

INSERT INTO `notification_template`
(`id`, `content`, `subject`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`)
VALUES (
35,
'<p>You can now view the latest test quality information for:</p> <strong>${orgName}</strong><br/>${orgRef}<br/><br/> <p>This information will help you identify anomalies and improve MOT test quality at your site(s).</p> <p> Go to <a id="ae-tqi-monthly-notification-${id}" href="${url}">service reports</a> to view test quality information',
'New test quality information available',
@app_user_id,
CURRENT_TIMESTAMP (6),
@app_user_id,
CURRENT_TIMESTAMP (6)
);