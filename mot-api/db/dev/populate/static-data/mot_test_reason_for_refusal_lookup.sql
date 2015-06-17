LOCK TABLES `mot_test_reason_for_refusal_lookup` WRITE;
/*!40000 ALTER TABLE `mot_test_reason_for_refusal_lookup` DISABLE KEYS */;

INSERT INTO `mot_test_reason_for_refusal_lookup` (`id`, `reason`, `reason_cy`, `code`, `display_order`, `mot1_legacy_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`, `version`, `batch_number` ) VALUES
('26','VTS not authorised to test vehicle class','VTS heb ei awdurdodi i arbrofi dosbarth y cerbyd','26','12','26','1','2014-12-04 15:59:17.825771',NULL,'2015-04-30 15:15:08.866582','1','0'),
('11','Motorcycle frame stamped not for road use','Ffram motorbeic wedi ei stampio ddim i\'w ddefnyddio ar y ffordd','11','11','11','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.784488','1','0'),
('10','Suspect maintenance history of diesel engine','Hanes cynhaliaeth amheuol o beiriant diesel','10','10','10','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.783299','1','0'),
('9','Requested test fee not paid in advance','Ffi arborfi gofynedig heb ei dalu yn flaenorol','9','9','9','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.782176','1','0'),
('8','Inspection may be dangerous or cause damage','Archiwliad yn beryglus neu yn achosi niwed','8','8','8','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.780946','1','0'),
('7','Unable to open device (door, boot, etc.)','Methu agor dyfais (drŵs,lledrgist ayyb.)','7','7','7','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.779594','1','0'),
('6','Vehicle emits substantial smoke','Cerbyd yn alltafu mŵg sylweddol','6','6','6','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.778483','1','0'),
('5','Vehicle configuration/size unsuitable','Cerbyd cyfluniad/maint anaddas','5','5','5','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.777222','1','0'),
('4','Insecurity of load or other items','Llwyth neu eitemau eraill yn anniogel','4','4','4','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.775522','1','0'),
('3','The vehicle is not fit to be driven','Cerbyd ddim yn ffit i’w ddreifio','3','3','3','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.773542','1','0'),
('2','Vehicle is too dirty to examine','Cerbyd rhy fydr i’w archwilio','2','2','2','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.772020','1','0'),
('1','Unable to identify date of first use','Methu unieithu dyddiad defnyddwyd gyntaf','1','1','1','1','2014-12-04 15:59:17.825771',NULL,'2015-02-17 10:23:37.770635','1','0');

/*!40000 ALTER TABLE `mot_test_reason_for_refusal_lookup` ENABLE KEYS */;
UNLOCK TABLES;
