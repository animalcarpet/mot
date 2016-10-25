SET @`app_user_id` = (SELECT `id`
                      FROM `person`
                      WHERE `user_reference` = 'Static Data' OR `username` = 'static data');

SET @insert_date = CURRENT_TIMESTAMP(6);


delete from dvla_model_model_detail_code_map 
where dvla_make_code = 'BB'
and   dvla_model_code in (
'001','002','003','004','005','007','008','009',
'010','011','012','013','014','015','016','017','018','019',
'020','021','022','026','027','028','029',
'030','031','048',
'050','051','052','053','061','062',
'070','071','074','075','076','077',
'084','085',
'106','112','113','114',
'120','122','123','124','125','126','127','128','129',
'130','131','132','133','134','135','136','137','138','139',
'140','141','142','143','144','145','147','148','149',
'150','151','152','153','154','155','158','159',
'160','161','162','163','164','165','166','167','168','169',
'170','171','172','173','174',
'182','183','184','185','186','187','188','189',
'190','191','192','193','194','195','196','197','198','199',
'200','201','202','203','204','205','206','207','208','209',
'210','211','212','213','214','215','216','217','218','219',
'220','221','224','226','227','229',
'230','231','232','233','234','235','236','237','238','239',
'240','241','242','243','244','245','246','247','248','249',
'250','251','252','253','254','255','256','257','258','259',
'260','261','262','263','264','265'
		);

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','001', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='001')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='001')); #"MINI ONE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','002', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='002')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='002')); #"MINI COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','003', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='003')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='003')); #"MINI ONE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','004', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='004')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='004')); #"MINI COOPER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','005', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='005')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='005')); #"MINI COOPER S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','007', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='007')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='007')); #"MINI COOPER S AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','008', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='008')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='008')); #"MINI ONE D E4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','009', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='009')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='009')); #"MINI ONE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','010', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='010')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='010')); #"MINI ONE CVT"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','011', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='011')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='011')); #"MINI ONE SEVEN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','012', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='012')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='012')); #"MINI ONE SEVEN CVT"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','013', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='013')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='013')); #"MINI COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','014', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='014')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='014')); #"MINI COOPER CVT"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','015', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='015')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='015')); #"MINI COOPER PARK LANE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','016', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='016')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='016')); #"MINI COOPER PARK LANE CVT"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','017', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='017')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='017')); #"MINI COOPER S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','018', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='018')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='018')); #"MINI COOPER S CHECKMATE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','019', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='019')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='019')); #"MINI COOPER S CHECKMATE A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','020', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='020')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='020')); #"COOPER S JCW"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','021', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='021')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='021')); #"COOPER S CHECKMATE JCW"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','022', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='022')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='022')); #"COOPER S JCW GP"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','026', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='026')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='026')); #"COOPER SIDEWALK"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','027', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='027')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='027')); #"COOPER SIDEWALK AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','028', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='028')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='028')); #"COOPER S SIDEWALK"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','029', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='029')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='029')); #"COOPER S SIDEWALK AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','030', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='030')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='030')); #"COOPER S SIDEWALK JCW"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','031', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='031')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='031')); #"ONE SIDEWALK"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','048', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='048')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='048')); #"COOPER AUTO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','050', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='050')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='050')); #"COOPER S AUTO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','051', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='051')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='051')); #"COOPER D CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','052', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='052')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='052')); #"COOPER D AUTO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','053', (select id from make where name = 'MINI'),(select id from model where name = 'JOHN COOPER WORKS' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='053')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='053')); #"MINI JOHN COOPER WORKS"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','061', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='061')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='061')); #"COOPER D GRAPHITE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','062', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='062')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='062')); #"COOPER D GRAPHITE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','070', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='070')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='070')); #"COOPER CAMDEN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','071', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='071')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='071')); #"COOPER CAMDEN D AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','074', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='074')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='074')); #"COOPER MAYFAIR D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','075', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='075')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='075')); #"COOPER MAYFAIR D AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','076', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='076')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='076')); #"COOPER MAYFAIR S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','077', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='077')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='077')); #"COOPER MAYFAIR S AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','084', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='084')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='084')); #"COOPER GRAPHITE CLUBMAN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','085', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='085')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='085')); #"COOPER GRAPHITE C-MAN D A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','106', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='106')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='106')); #"COOPER CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','112', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='112')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='112')); #"COOPER S CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','113', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='113')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='113')); #"COOPER S MAYFAIR"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','114', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='114')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='114')); #"COOPER S MAYFAIR AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','120', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='120')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='120')); #"ONE MINIMALIST 98"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','122', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='122')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='122')); #"COOPER CLUBMAN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','123', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='123')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='123')); #"ONE D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','124', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='124')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='124')); #"ONE CLUBMAN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','125', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='125')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='125')); #"COOPER S SOHO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','126', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='126')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='126')); #"COOPER S SOHO CLUBMAN A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','127', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='127')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='127')); #"COOPER SOHO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','128', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='128')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='128')); #"COOPER SOHO CLUBMAN A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','129', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='129')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='129')); #"COOPER SOHO CLUBMAN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','130', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='130')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='130')); #"COUNTRYMAN COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','131', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='131')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='131')); #"COUNTRYMAN COOPER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','132', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='132')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='132')); #"COUNTRYMAN COOPER D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','133', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='133')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='133')); #"COUNTRYMAN COOPER D ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','134', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='134')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='134')); #"COUNTRYMAN COOPER S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','135', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='135')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='135')); #"COUNTRYMAN COOPER S ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','136', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='136')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='136')); #"COUNTRYMAN COOPER S ALL4A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','137', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='137')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='137')); #"COUNTRYMAN COOPER S AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','138', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='138')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='138')); #"COUNTRYMAN ONE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','139', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='139')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='139')); #"COUNTRYMAN ONE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','140', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='140')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='140')); #"COUNTRYMAN ONE D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','141', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='141')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='141')); #"ONE SOHO CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','142', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='142')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='142')); #"ONE SOHO CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','143', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='143')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='143')); #"ONE SOHO CLUBMAN D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','144', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='144')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='144')); #"COOPER CLUBMAN HAMPTON"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','145', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='145')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='145')); #"COOPER CLUBMAN HAMPTON AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','147', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='147')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='147')); #"COOPER D CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','148', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='148')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='148')); #"COOPER D CLUBMAN HAMPTON"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','149', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='149')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='149')); #"COOPER D CLUBMAN HAMPTON AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','150', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='150')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='150')); #"COOPER D PIMLICO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','151', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='151')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='151')); #"COOPER D PIMLICO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','152', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='152')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='152')); #"COOPER PIMLICO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','153', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='153')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='153')); #"COOPER PIMLICO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','154', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='154')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='154')); #"COOPER S CLUBMAN HAMPTON"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','155', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='155')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='155')); #"COOPER S CLUBMAN HAMPTON AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','158', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='158')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='158')); #"COOPER SD CLUBMAN"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','159', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='159')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='159')); #"COOPER SD CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','160', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='160')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='160')); #"COOPER SD CLUBMAN HAMPTON"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','161', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='161')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='161')); #"COOPER SD CLUBMAN HAMPTON AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','162', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='162')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='162')); #"COUNTRYMAN COOPER D ALL4 AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','163', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='163')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='163')); #"COUNTRYMAN COOPER D AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','164', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='164')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='164')); #"COUNTRYMAN COOPER SD"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','165', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='165')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='165')); #"COUNTRYMAN COOPER SD ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','166', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='166')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='166')); #"COUNTRYMAN COOPER SD ALL4 AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','167', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='167')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='167')); #"COUNTRYMAN COOPER SD AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','168', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='168')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='168')); #"ONE D PIMLICO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','169', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='169')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='169')); #"ONE PIMLICO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','170', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='170')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='170')); #"ONE PIMLICO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','171', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='171')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='171')); #"COOPER D SOHO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','172', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='172')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='172')); #"COOPER D SOHO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','173', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='173')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='173')); #"COOPER SOHO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','174', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='174')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='174')); #"COOPER SOHO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','182', (select id from make where name = 'MINI'),(select id from model where name = 'INSPIRED BY GOODWOOD' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='182')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='182')); #"INSPIRED BY GOODWOOD"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','183', (select id from make where name = 'MINI'),(select id from model where name = 'INSPIRED BY GOODWOOD' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='183')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='183')); #"INSPIRED BY GOODWOOD AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','184', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='184')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='184')); #"MINI ROADSTER COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','185', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='185')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='185')); #"MINI ROADSTER COOPER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','186', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='186')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='186')); #"MINI ROADSTER COOPER S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','187', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='187')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='187')); #"MINI ROADSTER COOPER S AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','188', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='188')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='188')); #"MINI ROADSTER COOPER SD"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','189', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='189')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='189')); #"MINI ROADSTER COOPER SD AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','190', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='190')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='190')); #"MINI ROADSTER JOHNCOOPERWORKS"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','191', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='191')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='191')); #"COOPER BAKER STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','192', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='192')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='192')); #"COOPER BAKER STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','193', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='193')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='193')); #"COOPER BAYSWATER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','194', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='194')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='194')); #"COOPER BAYSWATER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','195', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='195')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='195')); #"COOPER D BAKER STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','196', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='196')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='196')); #"COOPER D BAKER STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','197', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='197')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='197')); #"COOPER D BAYSWATER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','198', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='198')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='198')); #"COOPER D BAYSWATER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','199', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='199')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='199')); #"COOPER D HIGHGATE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','200', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='200')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='200')); #"COOPER D HIGHGATE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','201', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='201')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='201')); #"COOPER D LONDON 2012 EDITION A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','202', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='202')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='202')); #"COOPER D LONDON 2012 EDITION"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','203', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='203')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='203')); #"COOPER HIGHGATE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','204', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='204')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='204')); #"COOPER HIGHGATE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','205', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='205')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='205')); #"COOPER LONDON 2012 EDITION A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','206', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='206')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='206')); #"COOPER LONDON 2012 EDITION"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','207', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='207')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='207')); #"COOPER S BAYSWATER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','208', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='208')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='208')); #"COOPER S BAYSWATER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','209', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='209')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='209')); #"COOPER S HIGHGATE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','210', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='210')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='210')); #"COOPER S HIGHGATE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','211', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='211')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='211')); #"COOPER S LONDON 2012 EDITION"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','212', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='212')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='212')); #"COOPER S LONDON 2012 EDITION A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','213', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='213')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='213')); #"COOPER SD BAYSWATER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','214', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='214')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='214')); #"COOPER SD BAYSWATER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','215', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='215')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='215')); #"COOPER SD HIGHGATE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','216', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='216')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='216')); #"COOPER SD HIGHGATE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','217', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='217')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='217')); #"COOPER SD LONDON 2012 EDITION"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','218', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='218')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='218')); #"COOPER SD LONDON 2012 EDITIONA"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','219', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='219')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='219')); #"ONE BAKER STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','220', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='220')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='220')); #"ONE BAKER STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','221', (select id from make where name = 'MINI'),(select id from model where name = 'ONE' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='221')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='221')); #"ONE D BAKER STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','224', (select id from make where name = 'MINI'),(select id from model where name = 'JOHN COOPER WORKS' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='224')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='224')); #"JOHN COOPER WORKS CLUBMAN AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','226', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='226')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='226')); #"COUNTRYMAN JOHN COOPER WORKS A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','227', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='227')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='227')); #"COUNTRYMAN JOHN COOPER WORKS"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','229', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='229')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='229')); #"ROADSTER JOHN COOPER WORKS"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','230', (select id from make where name = 'MINI'),(select id from model where name = 'ROADSTER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='230')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='230')); #"ROADSTER JOHN COOPER WORKS A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','231', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='231')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='231')); #"CLUBVAN COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','232', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='232')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='232')); #"CLUBVAN COOPER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','233', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='233')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='233')); #"CLUBVAN COOPER D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','234', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='234')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='234')); #"CLUBVAN COOPER D AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','235', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='235')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='235')); #"CLUBVAN ONE"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','236', (select id from make where name = 'MINI'),(select id from model where name = 'CLUBVAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='236')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='236')); #"CLUBVAN ONE AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','237', (select id from make where name = 'MINI'),(select id from model where name = 'JOHN COOPER WORKS' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='237')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='237')); #"JOHN COOPER WORKS GP"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','238', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='238')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='238')); #"PACEMAN COOPER"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','239', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='239')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='239')); #"PACEMAN COOPER AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','240', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='240')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='240')); #"PACEMAN COOPER D"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','241', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='241')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='241')); #"PACEMAN COOPER D ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','242', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='242')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='242')); #"PACEMAN COOPER D ALL4 AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','243', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='243')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='243')); #"PACEMAN COOPER D AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','244', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='244')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='244')); #"PACEMAN COOPER S"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','245', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='245')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='245')); #"PACEMAN COOPER S ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','246', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='246')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='246')); #"PACEMAN COOPER S ALL4 AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','247', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='247')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='247')); #"PACEMAN COOPER S AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','248', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='248')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='248')); #"PACEMAN COOPER SD"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','249', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='249')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='249')); #"PACEMAN COOPER SD ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','250', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='250')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='250')); #"PACEMAN COOPER SD ALL4 AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','251', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='251')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='251')); #"PACEMAN COOPER SD AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','252', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='252')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='252')); #"COOPER BOND STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','253', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='253')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='253')); #"COOPER BOND STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','254', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='254')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='254')); #"COOPER D BOND STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','255', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='255')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='255')); #"COOPER D BOND STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','256', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='256')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='256')); #"COOPER S BOND STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','257', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='257')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='257')); #"COOPER S BOND STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','258', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='258')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='258')); #"COOPER SD BOND STREET"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','259', (select id from make where name = 'MINI'),(select id from model where name = 'COOPER S' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='259')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='259')); #"COOPER SD BOND STREET AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','260', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='260')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='260')); #"PACEMAN JOHN COOPER WORKS AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','261', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='261')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='261')); #"PACEMAN JOHN COOPER WORKS"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','262', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='262')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='262')); #"COUNTRYMAN COOPER ALL4 TURBO A"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','263', (select id from make where name = 'MINI'),(select id from model where name = 'COUNTRYMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='263')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='263')); #"COUNTRYMAN COOPER ALL4"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','264', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='264')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='264')); #"PACEMAN COOPER ALL4 TURBO AUTO"
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id,
                                              created_by, created_on, last_updated_by, last_updated_on)
(select 'BB','265', (select id from make where name = 'MINI'),(select id from model where name = 'PACEMAN' and make_id = (select id from make where name = 'MINI')),
  @app_user_id,  @insert_date, @app_user_id,  @insert_date from dual
where exists (select 1 from dvla_model where make_code = 'BB' and code ='265')  and not exists 
(select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'BB' and dvla_model_code ='265')); #"PACEMAN COOPER ALL4"


select count(*) mini_model_mapping_replaced , 'Approx. 177 expected' expected_count
from dvla_model_model_detail_code_map
where created_on >= @insert_date
and  make_id = (select id from make where name = 'MINI');

