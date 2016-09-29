#!/usr/bin/env bash

# Params:
# [user to execute as] - default root
# [password] - default password
# [host] - default localhost

set -o errexit
MyUSER=${1-"root"}
MyPASS=${2-"password"}
MyHOST=${3-"localhost"}

for UPDATE_DIR in ./*/; do
    echo "$(date) Running DB upgrade for $UPDATE_DIR"
    cd $UPDATE_DIR
    ./db_upgrade.sh $MyUSER $MyPASS $MyHOST
    cd ..
done

