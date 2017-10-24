
#Adjust start_date to suit your needs. May next year is being used for now.
set @start_date = '2018-05-20';
set @app_user_id = 2;

#These deletes will only all work if none of these have been assigned to tests.
#Included here to make script re-runnable.
DELETE FROM rfr_vehicle_class_map where rfr_id > 19999;
DELETE FROM rfr_language_content_map where rfr_id > 19999;
DELETE FROM reason_for_rejection_hist where id > 19999;
DELETE FROM reason_for_rejection where id > 19999;
DELETE FROM test_item_category_vehicle_class_map where test_item_category_id > 19999;
DELETE FROM ti_category_language_content_map where test_item_category_id > 19999;
DELETE FROM test_item_category_hist where id > 19999 order by id desc;
DELETE FROM test_item_category where id > 19999 order by id desc;

-- Future end date Pre-EU Components so that EU Roadworthiness RFRs can be added and tested
UPDATE `mot2`.test_item_category
SET end_date = @start_date
WHERE end_date is null
AND id < 20000;

-- Future end date RFRs so that EU Roadworthiness RFRs can be added and tested
UPDATE `mot2`.reason_for_rejection
SET end_date = @start_date
WHERE end_date is null
AND id < 20000;

# New test EU test_item_category entries.

INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20000,    0,20000,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20001,20000,20000,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20002,20000,20000,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20010,    0,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20011,20010,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20012,20010,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20013,20010,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20014,20010,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20015,20010,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20020,20013,20010,@start_date,NULL,@app_user_id);
INSERT INTO `test_item_category` (`id`,`parent_test_item_category_id`,`section_test_item_category_id`,`start_date`,`end_date`,`created_by`) VALUES (20021,20013,20010,@start_date,NULL,@app_user_id);

# New test EU test_item_category_vehicle_class_map entries.
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20000,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20000,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20000,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20000,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20001,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20001,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20001,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20001,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20002,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20002,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20002,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20002,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20010,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20010,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20010,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20010,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20011,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20011,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20011,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20011,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20012,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20012,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20012,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20012,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20013,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20013,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20013,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20013,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20014,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20014,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20014,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20014,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20015,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20015,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20015,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20015,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20020,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20020,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20020,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20020,7,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20021,3,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20021,4,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20021,5,@app_user_id);
INSERT INTO `test_item_category_vehicle_class_map` (`test_item_category_id`,`vehicle_class_id`,`created_by`) VALUES (20021,7,@app_user_id);

#new test ti_language_content_map entries
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20000,1,'EU - Identification of the vehicle',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20000,2,'EU - Identification of the vehicle welsh',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20001,1,'Registration plate','Registration plate',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20001,2,'Plât cofrestru:','Plât cofrestru:',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20002,1,'Vehicle Identification Number','Vehicle Identification Number',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20002,2,'Rhif adnabod cerbyd vin:','Rhif adnabod cerbyd vin:',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20010,1,'EU - Visibility',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20010,2,'EU - Visibility (Welsh)',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20011,1,'Driver\'s view','Driver\'s view',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20011,2,'Driver\'s view (Welsh)',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20012,1,'Bonnet','Bonnet',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20012,2,'Boned:',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20013,1,'Condition of glass',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20013,2,'Condition of glass (Welsh)',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20014,1,'View to rear','View to rear',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20014,2,'View to rear (Welsh)',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20015,1,'Wipers','Wipers',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20015,2,'Wipers (Welsh)',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20020,1,'Windscreen','Windscreen',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20020,2,'Ffen:estr flaen:',NULL,@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20021,1,'Window','Window',@app_user_id);
INSERT INTO `ti_category_language_content_map` (`test_item_category_id`,`language_lookup_id`,`name`,`description`,`created_by`) VALUES (20021,2,'Ffenestr',NULL,@app_user_id);

# new test RFRs
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21000,20001,'Identification of the vehicle > Registration plates','Identification of the vehicle > Registration plates','0.1 (a)',0,0,0,0,'3',0,0,1,20000,1,NULL,'B',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21001,20001,'Identification of the vehicle > Registration plates','Identification of the vehicle > Registration plates','0.1 (b)',0,0,0,0,'3',0,0,1,20000,1,NULL,'B',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21002,20001,'Identification of the vehicle > Registration plates','Identification of the vehicle > Registration plates','0.1 (c)',0,0,0,0,'3',0,0,1,20000,1,NULL,'B',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21003,20001,'Identification of the vehicle > Registration plates','Identification of the vehicle > Registration plates','0.1 (d)',0,0,0,0,'3',0,0,1,20000,0,NULL,'t',@start_date,NULL,3,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21010,20002,'Identification of the vehicle > Vehicle Identification Number','Identification of the vehicle > Vehicle Identification Number','0.2 (a)',0,0,0,0,'3',0,0,1,20000,1,NULL,'t',@start_date,NULL,1,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21011,20002,'Identification of the vehicle > Vehicle Identification Number','Identification of the vehicle > Vehicle Identification Number','0.2 (b)',0,0,0,0,'3',0,0,1,20000,0,NULL,'B',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21020,20011,'Visibility > Driver\'s view','Visibility > Driver\'s view','3.1a(i)',0,0,0,0,'3',0,0,1,20010,0,NULL,'b',@start_date,NULL,3,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21021,20011,'Visibility > Driver\'s view','Visibility > Driver\'s view','3.1a(ii)',0,0,0,0,'3',0,0,1,20010,1,NULL,'b',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21022,20012,'Visibility > Bonnet','Visibility > Bonnet','3.1b(i)',0,0,0,0,'3',0,0,1,20010,0,NULL,'b',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21023,20012,'Visibility > Bonnet','Visibility > Bonnet','3.1b(ii)',0,0,0,0,'3',0,0,1,20010,1,NULL,'b',@start_date,NULL,1,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21024,20012,'Visibility > Bonnet','Visibility > Bonnet','3.1c(i)',0,0,0,0,'3',0,0,1,20010,1,NULL,'b',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21030,20021,'Visibility > Condition of glass > Window','Visibility > Condition of glass > Window','3.2a(i)',0,0,0,0,'3',0,0,1,20010,0,NULL,'V',@start_date,NULL,3,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21031,20021,'Visibility > Condition of glass > Window','Visibility > Condition of glass > Window','3.2a(ii)',0,0,0,0,'3',0,0,1,20010,0,NULL,'V',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21032,20020,'Visibility > Condition of glass > Windscreen','Visibility > Condition of glass > Windscreen','3.2a(i)',0,0,0,0,'3',0,0,1,20010,0,NULL,'t',@start_date,NULL,3,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21033,20020,'Visibility > Condition of glass > Windscreen','Visibility > Condition of glass > Windscreen','3.2a(ii)',0,0,0,0,'3',0,0,1,20010,0,NULL,'t',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21034,20014,'Visibility > View to rear','Visibility > View to rear','3.3a',0,0,0,0,'3',0,0,1,20010,0,NULL,'B',@start_date,NULL,2,@app_user_id);
INSERT INTO `reason_for_rejection` (`id`,`test_item_category_id`,`test_item_selector_name`,`test_item_selector_name_cy`,`inspection_manual_reference`,`minor_item`,`location_marker`,`qt_marker`,`note`,`manual`,`spec_proc`,`is_advisory`,`is_prs_fail`,`section_test_item_selector_id`,`can_be_dangerous`,`date_first_used`,`audience`,`start_date`,`end_date`,`rfr_deficiency_category_id`,`created_by`) VALUES (21040,20015,'Visibility > Windscreen Wipers','Visibility > Windscreen Wipers','3.4a',0,0,0,0,'3',0,0,1,20010,0,NULL,'t',@start_date,NULL,2,@app_user_id);

# New test EU rfr_vehicle_class_map entries.
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21000,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21000,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21000,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21000,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21001,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21001,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21001,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21001,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21002,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21002,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21002,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21002,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21003,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21003,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21003,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21003,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21010,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21010,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21010,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21010,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21011,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21011,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21011,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21011,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21020,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21020,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21020,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21020,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21021,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21021,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21021,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21021,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21022,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21022,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21022,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21022,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21023,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21023,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21023,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21023,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21024,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21024,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21024,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21024,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21030,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21030,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21030,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21030,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21031,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21031,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21031,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21031,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21032,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21032,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21032,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21032,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21033,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21033,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21033,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21033,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21034,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21034,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21034,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21034,7,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21040,3,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21040,4,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21040,5,@app_user_id);
INSERT INTO `rfr_vehicle_class_map` (`rfr_id`,`vehicle_class_id`,`created_by`) VALUES (21040,7,@app_user_id);

#new EU test rfr_language_content_map entries
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21000,1,'missing or insecure','Number plate missing or so insecure that it is likely to fall off.',NULL,'Identification of the vehicle > Registration plate',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21000,2,'missing or insecure (Welsh)','Number plate missing or so insecure that it is likely to fall off. (Welsh)',NULL,'',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21001,1,'inscription missing or illegible.','Number plate inscription missing or illegible.',NULL,'Identification of the vehicle > Registration plate',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21001,2,'inscription missing or illegible. (Welsh)','Number plate inscription missing or illegible. (Welsh)',NULL,'',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21002,1,'incorrect registration','Number plate showing an incorrect registration',NULL,'Identification of the vehicle > Registration plate',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21002,2,'incorrect registration (Welsh)','Number plate showing an incorrect registration (Welsh)',NULL,'',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21003,1,'does not conform to the specified requirements.','Number plate does not conform to the specified requirements.',NULL,'Identification of the vehicle > Registration plate',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21003,2,'does not conform to the specified requirements. (Welsh)','Welsh Number plate does not conform to the specified requirements.',NULL,'',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21010,1,'missing or cannot be found.','VIN missing or cannot be found.',NULL,'Identification of the vehicle > Vehicle Identification Number',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21010,2,'missing or cannot be found. (Welsh)','Welsh VIN missing or cannot be found.',NULL,'',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21011,1,'incomplete, illegible or obviously falsified.','VIN incomplete, illegible or obviously falsified',NULL,'Identification of the vehicle > Vehicle Identification Number',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21011,2,'incomplete, illegible or obviously falsified. (Welsh)','Welsh VIN incomplete, illegible or obviously falsified',NULL,'Identification of the vehicle > Vehicle Identification Number',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21020,1,'materially obstructed to the front ','An obstruction within the driver\'s field of view that materially affects his view in front or to the sides outside the swept area of the windscreen','an obstruction within the driver\'s field of view that materially affects his view in front or to the sides outside the swept area of the windscreen','Visibility > Driver\'s view',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21020,2,'materially obstructed to the front ','An obstruction within the driver\'s field of view that materially affects his view in front or to the sides outside the swept area of the windscreen (Welsh)',NULL,'Visibility > Driver\'s view',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21021,1,'of the road materially obstructed or an obligatory external mirror not visible','An obstruction materially affecting the driver\'s view of the rows through the swept area of the windscreen or an obligatory external mirror not visible.',NULL,'Visibility > Driver\'s view',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21021,2,'of the road materially obstructed or an obligatory external mirror not visible','An obstruction materially affecting the driver\'s view of the rows through the swept area of the windscreen or an obligatory external mirror not visible.',NULL,'Visibility > Driver\'s view',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21022,1,'cannot be safely secured in the closed position','A bonnet which cannot be safely secured in the closed position',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21022,2,'cannot be safely secured in the closed position (Welsh)','A bonnet which cannot be safely secured in the closed position (Welsh)',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21023,1,'is seriously at risk of opening inadvertently','A bonnet seriously at risk of opening inadvertently. ',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21023,2,'is seriously at risk of opening inadvertently (Welsh)','A bonnet seriously at risk of opening inadvertently. ',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21024,1,'with retaining device excessively deteriorated, ineffective or insecure','A bonnet primary retaining device excessively deteriorated, ineffective or insecure.',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21024,2,'with retaining device excessively deteriorated, ineffective or insecure','A bonnet primary retaining device excessively deteriorated, ineffective or insecure.',NULL,'Visibility > Bonnet',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21030,1,'damaged or seriously discoloured but not adversely affecting driver\'s view','Window damaged or seriously discoloured but not adversely affecting driver\'s view',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21030,2,'damaged or seriously discoloured but not adversely affecting driver\'s view','Window damaged or seriously discoloured but not adversely affecting driver\'s view',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21031,1,'damaged or seriously discoloured and affecting the driver\'s view of the road or an obligatory external mirror','Window damaged or seriously discoloured and affecting the driver\'s view of the road or of an obligatory external mirror.',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21031,2,'damaged or seriously discoloured but not adversely affecting driver\'s view','Window damaged or seriously discoloured and affecting the driver\'s view of the road or of an obligatory external mirror.',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21032,1,'excessively tinted but not adversely affecting driver\'s view','Windscreen excessively tinted but not adversely affecting driver\'s view',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21032,2,'excessively tinted but not adversely affecting driver\'s view','Windscreen excessively tinted but not adversely affecting driver\'s view',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21033,1,'excessively tinted and visibility through swept area of windscreen or of an obligatory external mirror seriously affected','Windscreen excessively tinted and visibility through swept area of windscreen or of an obligatory external mirror seriously affected',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21033,2,'excessively tinted and visibility through swept area of windscreen or of an obligatory external mirror seriously affected','Windscreen excessively tinted and visibility through swept area of windscreen or of an obligatory external mirror seriously affected',NULL,'Visibility - Condition of glass > window',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21034,1,'obligatory mirror or device missing','obligatory mirror or device missing',NULL,'Visibility - View of rear',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21034,2,'obligatory mirror or device missing','obligatory mirror or device missing',NULL,'Visibility - View of rear',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21040,1,'not operating or missing','Wiper not operating or missing',NULL,'Visibility - Windscreen wipers',@app_user_id);
INSERT INTO `rfr_language_content_map` (`rfr_id`,`language_type_id`,`name`,`inspection_manual_description`,`advisory_text`,`test_item_selector_name`,`created_by`) VALUES (21040,2,'not operating or missing','Wiper not operating or missing',NULL,'Visibility - Windscreen wipers',@app_user_id);

