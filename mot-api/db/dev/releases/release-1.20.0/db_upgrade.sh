upgradefiles=()
mysqladmin_password=PASSWORDGOESHERE
version=1.20.0
db_name=mot_v195_rel4

# add files here in intended run order for release
upgradefiles+=('2015-10-30-VM-12310-create-db-version-table-STORY.sql');
upgradefiles+=('2015-10-27-VM-12321-view-trade-roles-page-STORY.sql');
upgradefiles+=('2015-10-30-VM-12280-create-and-assign-VTS-test-logs-permission-STORY.sql')
upgradefiles+=('2015-10-27-VM-12328-certificate-replacement-additional-columns-only-STORY.sql');
upgradefiles+=('2015-10-30-VM-12328-replacement-certificate-draft-and-permissions-update-STORY.sql');
upgradefiles+=('2015-10-27-VM-12328-populate-mismatch-and-pass-flags-STORY.sql');
upgradefiles+=('2015-10-30-VM-12003-allow-user-with-tester-qualification-status-of-initial-training-required-to-do-a-training-test-STORY.sql');

for sqlscript in ${upgradefiles[@]}
do
  # run sql script via mysql client, time execution and capture error code
  errorCode=`/usr/bin/time -f "${sqlscript} took %E (%x)" mysql -t -vvv -h mysql -u mysql_admin -p${mysqladmin_password} ${db_name} < ${sqlscript} 2>&1 | tee -a /tmp/dbupgrade.log | tail -1 | awk -F '[()]' '{print $2}'`

  # if not successful, halt
  if [[ $errorCode -ne 0 ]]
  then
    echo "${sqlscript} caused upgrade failure, exiting. See /tmp/dbupgrade.log for details." | tee -a /tmp/dbupgrade.log
    exit 1
  fi

done

# if successful update DB version
mysql -h mysql -u mysql_admin -p${mysqladmin_password} ${db_name} -e "insert into database_version (version_name, applied_on) values ('${version}', current_timestamp());"

echo "DB upgrades applied successfully. DB version table updated with ${version}"
