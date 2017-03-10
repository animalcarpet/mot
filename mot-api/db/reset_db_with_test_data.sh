#!/bin/bash
#
# Recreate databse, repopulate it with test data (static and samples) and runs all updating scripts.
#
# Params:
# [user to execute as] - default root
# [password] - default root
# [host] - default localhost
# [user to be granted db access] - default motdbuser
# [dataset to load into db] - default synthetic

set -o errexit

shift $((OPTIND-1))

MyUSER=${1-"motdbuser"}
MyPASS=${2-"password"}
MyHOST=${3-"127.0.0.1"}
MyGRANTUSER=${4-"motdbuser"}
DATASET=${5-"synthetic"}
DATAFILE=${6-"NA"}

echo "$(date) Reset database mot2 with test data"

./drop_db.sh $MyUSER $MyPASS $MyHOST 2> >(grep -v 'Using a password on the command line interface can be insecure')
./create_db_with_test_data.sh $MyUSER $MyPASS $MyHOST $MyGRANTUSER $DATASET $DATAFILE 2> >(grep -v 'Using a password on the command line interface can be insecure')

echo "$(date) Done resetting DB"

