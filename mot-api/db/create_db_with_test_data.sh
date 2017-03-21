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
DATAFILE=${6-"NA"}

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

#Anonymised data on S3 storage
create_from_data_file() {
    echo Using data file $DATAFILE

    if [ ! -f ${DATAFILE} ]
    then
    	# download anonymised data from S3 and unzip the tar
        command -v aws >/dev/null 2>&1 || { echo >&2 "aws client not installed" ; }
        if [ $? != 0 ]
        then
            echo $(date) Using aws s3 cp s3://10k-anonymised-data/${DATAFILE} ${DATAFILE} to fetch data file
            aws s3 cp s3://10k-anonymised-data/${DATAFILE} ${DATAFILE}

            if [ $? != 0 ]
            then
                echo $(date) aws s3 cp s3://10k-anonymised-data/${DATAFILE} ${DATAFILE} returned non-zero
            fi 
        fi
    fi

    if [ ! -f ${DATAFILE} ]
    then
        echo $(date) Using curl -o ${DATAFILE} -# https://s3-eu-west-1.amazonaws.com/10k-anonymised-data/${DATAFILE} to get data file
        curl -o ${DATAFILE} -# https://s3-eu-west-1.amazonaws.com/10k-anonymised-data/${DATAFILE}

        if [ $? != 0 ]
        then
            echo $(date) curl -o ${DATAFILE} -# https://s3-eu-west-1.amazonaws.com/10k-anonymised-data/${DATAFILE} returned non-zero
        fi 
    fi

    if [ ! -f ${DATAFILE} ]
    then
        echo $(date) Database load file is not present - ${DATAFILE} check download permissions
        exit 1
    fi

    if [ -d temp-dev ]
    then
        rm -rf temp-dev/
    fi
    mkdir temp-dev
    tar -xzf ${DATAFILE} -C temp-dev

    # run the create scripts from inside the temp folder
    cd temp-dev
    create_with_data
    cd ..;

    rm -rf temp-dev/
}

run_each_release_db_update_script() {

    # We want the mysql to report an error but continue. This is a quick fix that
    # will allow the script to work.
    set +e

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

    # Switch it back on again...
    set -e
}

case "$DATASET" in
    synthetic)
        create_with_synthetic_data
        run_each_release_db_update_script
        ;;
    anonymised)
        if [ $DATAFILE == "NA" ]
        then 
            DATAFILE="dev-anonymised-R3.0.tgz"
        fi
        create_from_data_file
        run_each_release_db_update_script
        ;;
    10k)
        if [ $DATAFILE == "NA" ]
        then 
            DATAFILE="dev-10k.tgz"
        fi
        create_from_data_file

        #Rollforward test dates - not really good practice but retained previous behavior because others may like it.
        echo "$(date) Rolling forward test dates "
        mysql -u $MyUSER -p$MyPASS -h $MyHOST -D mot2 < $BASE_DIR/bring_forward_mot_test_dates.sql
        run_each_release_db_update_script
        ;;
    *)
        printf "\e[1;31mERROR: unrecognised argument: "$DATASET".\e[0m Valid arguments are 'synthetic' |  '10k' | 'anonymised' .\n"
        exit 2
        ;;
esac
