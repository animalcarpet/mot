SET @`app_user_id` = (SELECT `id`
                      FROM `person`
                      WHERE `user_reference` = 'Static Data' OR `username` = 'static data');
                      
set @is_verified = 1;
set @model_code = (select max(code) from model);
set @smart_make_id = (select id from make where name = 'SMART (MCC)');

#Make these models if they exist under SMATRT (MCC) already to verified
update model set is_verified = 1,
    code = ifnull(code,@model_code:=@model_code+1)
where make_id = @smart_make_id
and name in ('PURE', 'PULSE', 'PASSION', 'BRABUS', 'STARBLUE')
and is_verified = 0;

#Insert the above models under the SMART (MCC) make if they do not exist
insert into model (name, make_id, code, is_verified, created_by)
(select 'PURE', @smart_make_id, @model_code:=@model_code+1, @is_verified, @app_user_id from dual 
where not exists (select 1 from model where name = 'PURE' and make_id = @smart_make_id));
insert into model (name, make_id, code, is_verified, created_by)
(select 'PULSE', @smart_make_id, @model_code:=@model_code+1, @is_verified, @app_user_id from dual 
where not exists (select 1 from model where name = 'PULSE' and make_id = @smart_make_id));
insert into model (name, make_id, code, is_verified, created_by)
(select 'PASSION', @smart_make_id, @model_code:=@model_code+1, @is_verified, @app_user_id from dual 
where not exists (select 1 from model where name = 'PASSION' and make_id = @smart_make_id));
insert into model (name, make_id, code, is_verified, created_by)
(select 'BRABUS', @smart_make_id, @model_code:=@model_code+1, @is_verified, @app_user_id from dual 
where not exists (select 1 from model where name = 'BRABUS' and make_id = @smart_make_id));
insert into model (name, make_id, code, is_verified, created_by)
(select 'STARBLUE', @smart_make_id, @model_code:=@model_code+1, @is_verified, @app_user_id from dual 
where not exists (select 1 from model where name = 'STARBLUE' and make_id = @smart_make_id));

select map.dvla_make_code, map.dvla_model_code, mk.name dvsa_make_name, ml.name dvsa_model_name, map.created_on 
from dvla_model_model_detail_code_map map
join model ml on ml.id = map.model_id and ml.make_id = map.make_id
join make  mk on mk.id = map.make_id
where
dvla_make_code = 'LH' and
dvla_model_code in ( 
 '001', '002', '003', '004', '005', '006', '007', '008', '009', '010',
 '011', '012', '013', '014', '015', '016', '017', '018', '019', '020',
 '021', '022', '023', '024', '025', '026', '027', '028', '029', '030',
 '031');

#Replace some previously created mappings to SMART (MCC) make
delete from dvla_model_model_detail_code_map
where
dvla_make_code = 'LH' and
dvla_model_code in ( 
 '001', '002', '003', '004', '005', '006', '007', '008', '009', '010',
 '011', '012', '013', '014', '015', '016', '017', '018', '019', '020',
 '021', '022', '023', '024', '025', '026', '027', '028', '029', '030',
 '031');

# Insert revised mappings for SMART (MCC) models
insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','001', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='001')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='001'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','002', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='002')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='002'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','003', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='003')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='003'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','004', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='004')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='004'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','005', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='005')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='005'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','006', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='006')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='006'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','007', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='007')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='007'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','008', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='008')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='008'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','009', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='009')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='009'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','010', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='010')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='010'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','011', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='011')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='011'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','012', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='012')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='012'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','013', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='013')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='013'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','014', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='014')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='014'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','015', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='015')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='015'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','016', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='016')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='016'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','017', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='017')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='017'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','018', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='018')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='018'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','019', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='019')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='019'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','020', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='020')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='020'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','021', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='021')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='021'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','022', @smart_make_id,(select id from model where name = 'CROSSBLADE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='022')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='022'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','023', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='023')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='023'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','024', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='024')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='024'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','025', @smart_make_id,(select id from model where name = 'PULSE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='025')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='025'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','026', @smart_make_id,(select id from model where name = 'PASSION' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='026')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='026'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','027', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='027')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='027'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','028', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='028')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='028'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','029', @smart_make_id,(select id from model where name = 'PURE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='029')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='029'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','030', @smart_make_id,(select id from model where name = 'BRABUS' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='030')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='030'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LH','031', @smart_make_id,(select id from model where name = 'STARBLUE' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LH' and code ='031')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LH' and dvla_model_code ='031'));

select map.dvla_make_code, map.dvla_model_code, mk.name dvsa_make_name, ml.name dvsa_model_name, map.created_on 
from dvla_model_model_detail_code_map map
join model ml on ml.id = map.model_id and ml.make_id = map.make_id
join make  mk on mk.id = map.make_id
where
dvla_make_code = 'LH' and
dvla_model_code in ( 
 '001', '002', '003', '004', '005', '006', '007', '008', '009', '010',
 '011', '012', '013', '014', '015', '016', '017', '018', '019', '020',
 '021', '022', '023', '024', '025', '026', '027', '028', '029', '030',
 '031');
 

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','264', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='264')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='264'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','265', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='265')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='265'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','266', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='266')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='266'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','267', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='267')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='267'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','268', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='268')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='268'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','269', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='269')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='269'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','270', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='270')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='270'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','271', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='271')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='271'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','272', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='272')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='272'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','273', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='273')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='273'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','274', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='274')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='274'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','275', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='275')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='275'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','276', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='276')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='276'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','277', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='277')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='277'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','278', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='278')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='278'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','279', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='279')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='279'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','280', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='280')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='280'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','281', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='281')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='281'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','282', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='282')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='282'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','283', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='283')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='283'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','284', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='284')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='284'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','285', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='285')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='285'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','286', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='286')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='286'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','287', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='287')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='287'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','288', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='288')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='288'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','289', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='289')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='289'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','290', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='290')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='290'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','291', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='291')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='291'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','292', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='292')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='292'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','293', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='293')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='293'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','294', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='294')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='294'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','295', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='295')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='295'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','296', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='296')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='296'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','297', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='297')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='297'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','298', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='298')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='298'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','299', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='299')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='299'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','300', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='300')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='300'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','301', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='301')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='301'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','302', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='302')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='302'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','303', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='303')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='303'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','304', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='304')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='304'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','305', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='305')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='305'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','306', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='306')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='306'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','307', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='307')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='307'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','308', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='308')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='308'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','309', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='309')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='309'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','310', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='310')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='310'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','311', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='311')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='311'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','312', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='312')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='312'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','313', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='313')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='313'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','314', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='314')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='314'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','315', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='315')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='315'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','316', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='316')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='316'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','317', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='317')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='317'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','319', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='319')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='319'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','320', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='320')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='320'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','321', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='321')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='321'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','322', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='322')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='322'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','323', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='323')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='323'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','324', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='324')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='324'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','325', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='325')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='325'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','326', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='326')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='326'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','327', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='327')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='327'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','328', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='328')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='328'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','329', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='329')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='329'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','330', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='330')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='330'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','331', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='331')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='331'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','332', @smart_make_id,(select id from model where name = 'FORFOUR' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='332')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='332'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','333', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='333')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='333'));

insert into dvla_model_model_detail_code_map (dvla_make_code, dvla_model_code, make_id, model_id, created_by)
(select 'LK','334', @smart_make_id,(select id from model where name = 'FORTWO' and make_id = @smart_make_id),
  @app_user_id from dual 
where exists 
(select 1 from dvla_model where make_code = 'LK' and code ='334')  
and not exists (select 1 from dvla_model_model_detail_code_map where dvla_make_code = 'LK' and dvla_model_code ='334'));


select map.dvla_make_code, map.dvla_model_code, mk.name dvsa_make_name, ml.name dvsa_model_name, map.created_on 
from dvla_model_model_detail_code_map map
join model ml on ml.id = map.model_id and ml.make_id = map.make_id
join make  mk on mk.id = map.make_id
where
dvla_make_code = 'LK' and
dvla_model_code > '263' and 
dvla_model_code < '335';

