#!/usr/bin/env bash

workspace='/home/vagrant/mot';
phpRootDir='/opt/rh/php55/root';

declare -A mysql_config=(
    [user]=motdbuser
    [password]=password
    [host]=localhost
    [database]=mot
    [grantuser]=motdbuser
)

function mot__is_frontend_node() {
    if [[ "dev" == `hostname -s` ]]; then
        return 0;
    else
        return 1;
    fi
}

function mot__is_api_node() {
    if [[ "dev2" == `hostname -s` ]]; then
        return 0;
    else
        return 1;
    fi
}


# Restarts the Apache Web server
function mot__apache_restart() {
    sudo service httpd24-httpd restart
}

# Restarts the Nginx Web server
function mot__nginx_restart() {
    sudo service nginx restart
}

# Restarts the MySQL server
function mot__mysql_restart() {
    sudo service mysql restart
}

# Removes Doctrine proxies.
function mot__delete_doctrine_cache_folders() {
    pushd ${workspace}/mot-api/data 1>/dev/null && \
    echo -n "Removing ${workspace}/mot-api/data/DoctrineModule contents... "
    rm -rf DoctrineModule && \
    echo " done." && \
    echo -n "Removing ${workspace}/mot-api/data/DoctrineORMModule contents... "
    rm -fr DoctrineORMModule && \
    echo " done." && \
    popd 1>/dev/null
}

# Generates Doctrine proxies.
function mot__doctrine_proxy_gen() {
    echo "Generating Doctrine Proxies..."
    ${workspace}/mot-api/vendor/dvsa/scripts/jenkins/generate-proxies.sh
}

# Generates Doctrine proxies
function mot__doctrine_proxy() {
    mot__delete_doctrine_cache_folders;
    mot__doctrine_proxy_gen;
}

function mot__doctrine_default_develop_dist() {
    echo "Removing ${workspace}/mot-api/config/autoload/optimised.development.php"
    rm -f ${workspace}/mot-api/config/autoload/optimised.development.php
}

function mot__xdebug_disable() {
   sudo sed -i.bak "s/^\\s*zend_ext/;zend_ext/g" ${phpRootDir}/etc/php.d/xdebug.ini
}

function mot__xdebug_enable() {
   sudo sed -i.bak "s/;\\s*zend_ext/zend_ext/g" ${phpRootDir}/etc/php.d/xdebug.ini
}

function mot__xdebug_on() {
   sudo sed -i.bak "s/remote_autostart=0/remote_autostart=1/g" ${phpRootDir}/etc/php.d/xdebug.ini;
   sudo sed -i.bak "s/remote_enable=0/remote_enable=1/g" ${phpRootDir}/etc/php.d/xdebug.ini;
}

function mot__xdebug_off() {
   sudo sed -i.bak "s/remote_autostart=1/remote_autostart=0/g" ${phpRootDir}/etc/php.d/xdebug.ini;
   sudo sed -i.bak "s/remote_enable=1/remote_enable=0/g" ${phpRootDir}/etc/php.d/xdebug.ini;
}

function mot__test_php_frontend() {
    cd ${workspace}/mot-web-frontend && vendor/bin/phpunit
}

function mot__test_php_api() {
    cd ${workspace}/mot-api && vendor/bin/phpunit
}

function mot__test_php_common() {
    cd ${workspace}/mot-common-web-module && vendor/bin/phpunit
}

function mot__test_php() {
    mot__test_php_common;
    mot__test_php_api;
    mot__test_php_frontend;
}

function mot__fitnesse_run() {
    cd ${workspace}/mot-fitnesse;
    ./run.sh
}

function mot__fitnesse_suite() {
    cd ${workspace}/mot-fitnesse;
    ./run_ci.sh "FrontPage.SuiteAcceptanceTests?suite" text;
}

function mot__fitnesse_enforcement() {
    cd ${workspace}/mot-fitnesse;
    ./run_ci.sh "FrontPage.SuiteAcceptanceTests.EnforcementSuite?suite" text;
}

function mot__fitnesse_licensing() {
    cd ${workspace}/mot-fitnesse;
    ./run_ci.sh "FrontPage.SuiteAcceptanceTests.LicensingSuite?suite" text;
}

function mot__fitnesse_testing() {
    cd ${workspace}/mot-fitnesse;
    ./run_ci.sh "FrontPage.SuiteAcceptanceTests.TestingSuite?suite" text;
}

function mot__fitnesse_event() {
    cd ${workspace}/mot-fitnesse;
    ./run_ci.sh "FrontPage.SuiteAcceptanceTests.EventSuite?suite" text;
}

function mot__test_behat() {
    cd ${workspace}/mot-behat && bin/behat;
}

# Reset the database on the VM with the small data set.
function mot__reset_database() {
    export dev_workspace="${workspace}/";
    pushd ${workspace}/mot-api/db 1>/dev/null && \
    ./reset_db_with_test_data.sh && echo "DB Reset" \
    popd 1>/dev/null
}
# Reset the database on the VM with the small data set, without *_hist tables
# and triggers.
function mot__reset_database_no_hist() {
    export dev_workspace="${workspace}/";
    pushd ${workspace}/mot-api/db 1>/dev/null && \
    sudo ./reset_db_with_test_data.sh -f \
    ${mysql_config[user]} ${mysql_config[password]} ${mysql_config[host]} \
    ${mysql_config[database]} ${mysql_config[grantuser]} N N \
    && echo "DB Reset without *_hist tables"
}

# Dumps the database on the VM.
function mot__dump_database() {
    export dev_workspace="${workspace}/";
    pushd ${workspace}/mot-api/db/dev/bin 1>/dev/null && \
    sudo php ./dump_db.php && sudo mysqldump -d \
    --skip-add-drop-table -h ${mysql_config[host]} -u ${mysql_config[user]} \
    -p ${mysql_config[password]} ${mysql_config[database]} \
    > $dev_workspace/mot-api/db/dev/schema/create_dev_db_schema.sql && \
    echo "DB dump" && \
    popd 1>/dev/null
}

# Reset the database on the VM with the full sample data set.
function mot__reset_database_full() {
    export dev_workspace="${workspace}/";
    pushd ${workspace}/mot-api/db 1>/dev/null && \
    sudo ./reset_db_with_test_data.sh -f \
    ${mysql_config[user]} ${mysql_config[password]} ${mysql_config[host]} \
    ${mysql_config[database]} ${mysql_config[grantuser]} Y && \
    echo "DB Full Reset" && \
    popd 1>/dev/null
}

# Repair a broken mysql proc table in the VM.
function mot__mysql_proc_fix() {
    sudo mysql -u ${mysql_config[user]} -p${mysql_config[password]} -e \
    "use mysql; repair table mysql.proc;"
}

# NOTE: New in mot-vagrant.
function mot__import_data() {
    sudo /vagrant/scripts/import-data.sh
    echo ""
    echo "* IMPORTANT: The import-data.sh creates uncommited changes."
    echo "* Please make sure you clear them before commiting your work."
    echo "* This issue has been identified in https://gitlab.motdev.org.uk/webops/mot-vagrant/issues/13"
}

# Trace the API logs
function mot__trace_api_log() {
    sudo su -c "tail -F /var/log/httpd/mot-api_*.log /var/log/dvsa/mot-api.log /var/log/dvsa/mot-vehicle-service.log"
}

# Trace the web logs
function mot__trace_web_log() {
    sudo su -c "tail -F /var/log/httpd/dev.motdev.org.uk_*.log /var/log/dvsa/mot-webfrontend.log"
}

function mot__trace_web_logs() {
    sudo su -c "tail -F /var/log/httpd/*.log /var/log/dvsa/*.log"
}

# Reinstall OpenAM. NOTE: New in mot-vagrant.
function mot__openam_reinstall() {
    sudo /vagrant/scripts/reinstallopenam.sh
}

function mot__server_mod_prod() {
    sudo sed -i.bak "s/.*opcache.validate_timestamps=.*/opcache.validate_timestamps=0/g" ${phpRootDir}/etc/php.d/opcache.ini
}

function mot__server_mod_dev() {
    sudo sed -i.bak "s/^opcache.validate_timestamps.*/;opcache.validate_timestamps=0/g" ${phpRootDir}/etc/php.d/opcache.ini
}

# Switches the environment into optimised mode.
function mot__dev_optimise() {
    mot__is_api_node;
    local is_api=$?;
    mot__is_frontend_node;
    local is_frontend=$?;

    if [ $is_frontend -eq 0 ]; then mot__reset_database; fi;
    mot__xdebug_disable;
    mot__server_mod_prod;
    if [ $is_api -eq 0 ]; then mot__doctrine_proxy; fi;
    mot__apache_restart;
}

# Switches the environment into standard development mode.
function mot__dev_std() {
    mot__is_api_node;
    local is_api=$?;
    mot__is_frontend_node;
    local is_frontend=$?;

    mot__apache_restart;
    if [ $is_frontend -eq 0 ]; then mot__reset_database; fi

    mot__server_mod_dev;
    if [ $is_api -eq 0 ]; then mot__doctrine_default_develop_dist; fi;
    mot__apache_restart;
}

function mot__composer() {
    if type composer 2>/dev/null; then
        if [ -f /usr/local/bin/composer ]; then
            echo "Updating the composer binary..."
            sudo ${phpRootDir}/usr/bin/php /usr/local/bin/composer self-update
        fi
    else
        echo "composer was not found in this sytem. Installing..."
        curl -sS https://getcomposer.org/installer | sudo ${phpRootDir}/usr/bin/php -- --install-dir=/usr/local/bin --filename=composer
    fi

    pushd ${workspace} 1>/dev/null
    for dir in *; do
        if [[ -d "$dir" && ! -L "$dir" ]]; then
            cd $dir
            if [ -f composer.json ]; then
                echo "+ Updating deps in $dir"
                composer install
            fi
            cd ..
        fi
    done;
    popd 1>/dev/null
}

# Runs common tasks after switching branches.
function mot__switch_branch() {
    mot__is_api_node;
    local is_api=$?;
    mot__is_frontend_node;
    local is_frontend=$?;

    mot__apache_restart
    mot__composer
    if [ $is_frontend -eq 0 ]; then mot__mysql_proc_fix; fi;
    if [ $is_frontend -eq 0 ]; then mot__reset_database; fi;
    mot__server_mod_dev
    if [ $is_api -eq 0 ]; then mot__doctrine_default_develop_dist; fi;
    if [ $is_api -eq 0 ]; then mot__doctrine_proxy; fi;
    mot__apache_restart
}
