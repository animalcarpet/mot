#!/bin/bash
#
# Creates database, creates schema and grants privilages for given user
#
# Params:
# [user to execute as] - default root
# [password] - default root
# [host] - default localhost
# [user to be granted db access] - default motdbuser

set -o errexit
MyUSER=${1-"root"}
MyPASS=${2-"root"}
MyHOST=${3-"localhost"}
MyGRANTUSER=${4-"motdbuser"}

echo "$(date) Creating database mot2 on host $MyHOST for user $MyGRANTUSER"

mysql -u $MyUSER -p$MyPASS -h $MyHOST -Bse 'GRANT CREATE TEMPORARY TABLES, SELECT, UPDATE, INSERT, DELETE, EXECUTE ON *.* TO `'$MyGRANTUSER'`'

echo "$(date) Loading schema"
mysql -u $MyUSER -p$MyPASS -h $MyHOST < ./dev/schema/create_dev_db_schema.sql
