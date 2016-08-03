SET @app_user_id = (SELECT `id` FROM `person` WHERE `username` = 'static data' OR `user_reference` = 'Static Data');

DELETE site_contact FROM site_contact_detail_map site_contact
    JOIN site_contact_type type ON type.id = site_contact.site_contact_type_id
WHERE type.code = 'CORR';

DELETE map1 FROM site_contact_detail_map map1
    JOIN site_contact_detail_map map2 ON map1.site_id = map2.site_id
WHERE map1.id > map2.id;