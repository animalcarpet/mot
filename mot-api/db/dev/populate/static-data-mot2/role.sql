LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;

INSERT INTO `role` (`id`, `name`, `code`, `mot1_legacy_id`, `created_by`, `created_on`, `last_updated_by`, `last_updated_on`, `version`, `batch_number` ) VALUES
('28','','DVSA-AREA-OFFICE-2',NULL,'2','2015-02-17 10:23:31.289815',NULL,'2015-04-02 15:21:05.233596','1','0'),
('27','','DVLA-OPERATIVE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('26','','FINANCE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('25','','CUSTOMER-SERVICE-CENTRE-OPERATIVE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('24','','CUSTOMER-SERVICE-MANAGEMENT',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('23','','VEHICLE-EXAMINER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('22','','USER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('21','','TESTER-INACTIVE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('20','','TESTER-APPLICANT-INITIAL-TRAINING-REQUIR',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('19','','TESTER-APPLICANT-INITIAL-TRAINING-FAILED',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('18','','TESTER-APPLICANT-DEMO-TEST-REQUIRED',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('17','','TESTER-ACTIVE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('16','','TESTER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('15','','SLOT-PURCHASER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('14','','SITE-MANAGER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('13','','SITE-ADMIN',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('12','','GUEST',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('11','','DVSA-SCHEME-USER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('10','','DVSA-SCHEME-MANAGEMENT',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('9','','DVSA-AREA-OFFICE-1',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('8','','DEMOTEST',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('7','','CRON',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('6','','AUTHORISED-EXAMINER-PRINCIPAL',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('5','','AUTHORISED-EXAMINER-DESIGNATED-MANAGER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('4','','AUTHORISED-EXAMINER-DELEGATE',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('3','','AUTHORISED-EXAMINER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('2','','ASSESSMENT-LINE-MANAGER',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0'),
('1','','ASSESSMENT',NULL,'2','2014-12-04 15:59:18.326111',NULL,'2015-04-02 15:21:05.233596','1','0');

/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;
