#!/bin/bash
#
# Drops database
#
# Params:
# [user to execute as] - default root
# [password] - default root
# [host] - default localhost

set -o errexit
MyUSER=${1-"root"}
MyPASS=${2-"root"}
MyHOST=${3-"localhost"}

echo "$(date) Dropping database $MyDATABASE"
mysql -u $MyUSER -p$MyPASS -h $MyHOST -Bse 'DROP DATABASE IF EXISTS `mot2`;'
