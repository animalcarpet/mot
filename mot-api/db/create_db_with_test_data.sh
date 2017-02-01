#!/bin/bash
#
# Creates database (schema and tables structure), populates tables with static and test data. Runs all update scripts
#
# Params:
# [user to execute as] - default root
# [password] - default root
# [host] - default localhost
# [user to be granted db access] - default motdbuser
# [dataset to load into db] - default synthetic

set -o errexit
MyUSER=${1-"root"}
MyPASS=${2-"password"}
MyHOST=${3-"localhost"}
MyGRANTUSER=${4-"motdbuser"}
DATASET=${5-"synthetic"}

BASE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "$(date) Creating 'mot2' database and loading schema with $DATASET dataset"

create_with_data() {
    $BASE_DIR/create_db.sh $MyUSER $MyPASS $MyHOST $MyGRANTUSER

    # generate and load synthetic test data
    cat ./dev/populate/template/sql-header.tsql \
        ./dev/populate/static-data/*.sql \
        ./dev/populate/test-data/*.sql \
        ./dev/populate/template/sql-footer.tsql \
        > ./populate_db.sql_temp

    echo $(date) Loading test data into mot2 on $MyHOST as $MyUSER
    mysql -u $MyUSER -p$MyPASS -h $MyHOST -D mot2 < ./populate_db.sql_temp
    rm ./populate_db.sql_temp
}

create_with_synthetic_data() {
    $BASE_DIR/create_db.sh $MyUSER $MyPASS $MyHOST $MyGRANTUSER

    create_with_data
}

create_with_10k_data() {
    # download 10k data from S3 and unzip the tar
    mkdir temp-dev
    curl -o dev-10k.tgz -# https://s3-eu-west-1.amazonaws.com/10k-anonymised-data/dev-10k.tgz
    tar -xzf dev-10k.tgz -C temp-dev

    # run the create scripts from inside the temp folder
    cd temp-dev
    create_with_data
    cd ..;

     rm -rf temp-dev/

    mysql -u $MyUSER -p$MyPASS -h $MyHOST -D mot2 < $BASE_DIR/bring_forward_mot_test_dates.sql
}

run_each_release_db_update_script() {
    # load subsequent DB updates from the releases folder
    for UPDATE_DIR in $BASE_DIR/dev/releases/*/; do
        test -d $UPDATE_DIR || continue

        for UPDATE in $UPDATE_DIR/*.sql; do
            test -f $UPDATE || continue

            # skip NOT-FOR-PRODUCTION sql unless using synthetic dataset
            if [[ $DATASET != "synthetic" ]] && [[ $UPDATE == *"NOT-FOR-PRODUCTION"* ]]
            then
                continue
            fi

            echo "$(date) Loading schema or data update $UPDATE"
            mysql -u $MyUSER -p$MyPASS -h $MyHOST -D mot2 < $UPDATE
        done
    done
}

case "$DATASET" in
    synthetic)
        create_with_synthetic_data
        run_each_release_db_update_script
        ;;
    10k)
        create_with_10k_data
        run_each_release_db_update_script
        ;;
    *)
        printf "\e[1;31mERROR: unrecognised argument: "$DATASET".\e[0m Valid arguments are 'synthetic' and '10k.\n"
        exit 2
        ;;
esac
