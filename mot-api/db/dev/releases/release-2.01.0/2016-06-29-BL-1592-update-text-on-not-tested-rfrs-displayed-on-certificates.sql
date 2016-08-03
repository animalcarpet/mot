SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

UPDATE `ti_category_language_content_map`
SET `description`='Brake performance',
    `last_updated_by`=@app_user_id,
    `last_updated_on`=CURRENT_TIMESTAMP(6)
WHERE `name` = 'Brake performance not tested';

UPDATE `ti_category_language_content_map`
SET `description`='Headlamp aim',
    `last_updated_by`=@app_user_id,
    `last_updated_on`=CURRENT_TIMESTAMP(6)
WHERE `name` = 'Headlamp aim not tested';

UPDATE `ti_category_language_content_map`
SET `description`='Emissions',
    `last_updated_by`=@app_user_id,
    `last_updated_on`=CURRENT_TIMESTAMP(6)
WHERE `name` = 'Emissions not tested';
