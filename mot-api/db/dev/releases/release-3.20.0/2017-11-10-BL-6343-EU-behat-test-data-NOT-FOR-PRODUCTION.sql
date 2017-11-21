set @start_date_past = '2017-05-20';
set @end_date_past = '2017-10-20';
set @start_date_future = '2050-10-20';
set @end_date_future= '2051-10-20';
set @app_user_id = 2;

INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`created_by`)
VALUES (	90000	,	0	,	0	,@start_date_past,@app_user_id);

INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`created_by`)
VALUES (	90001	,	90000	,	90000	,@start_date_past,@app_user_id);

INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90000	,	3	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90000	,	4	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90000	,	5	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90000	,	7	,@app_user_id);

INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90001	,	3	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90001	,	4	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90001	,	5	,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (	90001	,	7	,@app_user_id);

INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (90000,1,'Identification of the vehicle',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (90001,1,'Registration plates','Registration plate',@app_user_id);

INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`, `test_item_selector_name_cy`,`section_test_item_selector_id`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`)
VALUES (90000,90001,'Behat test RFR','behat-test-start',0,'90000',1,0,0,0,3,0,0,1,0,NULL,'b',@start_date_past,NULL,2,@app_user_id);

INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`, `test_item_selector_name_cy`,`section_test_item_selector_id`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`)
VALUES (90001,90001,'behat','behat-test-end',0,'90000',1,0,0,0,3,0,0,1,0,NULL,'b',@start_date_past,@end_date_past,2,@app_user_id);

INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`, `test_item_selector_name_cy`,`section_test_item_selector_id`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`)
VALUES (90002,90001,'behat','behat-test-start-future',0,'90000',1,0,0,0,3,0,0,1,0,NULL,'b',@start_date_future,@end_date_future,2,@app_user_id);

INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,1,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,2,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90000,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,1,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,2,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90001,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,1,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,2,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (90002,7,@app_user_id);

INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`created_by`)
VALUES (90000,1,'Behat Test start date','Start date past can view',NULL,@app_user_id);

INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`created_by`)
VALUES (90001,1,'Behat test end date','End date in past can not view',NULL,@app_user_id);

INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`created_by`)
VALUES (90002,1,'Behat test start date in future','start date in future can not view',NULL,@app_user_id);

