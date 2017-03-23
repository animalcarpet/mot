
module.exports = function(grunt, config) {
    function updateJavaService(service) {
        return "cd <%= host_machine.vagrantDirectory %> && ./scripts/update_java_services.sh jar " + service + " build";
    }


    function resetDb(dataset) {
        return 'cd <%= host_machine.workspace %>/mot/mot-api/db && ' +
            'sudo ./reset_db_with_test_data.sh ' +
                '<%= mysql_config.user %> ' +
                '<%= mysql_config.password %> ' +
                '<%= host_machine.mysqlHost %> ' +
                '<%= mysql_config.grantuser %> ' +
                dataset;
    }

    grunt.config('shell', {
        jasper_remove: {
            command: "curl --user jasperadmin:jasperadmin -v -XDELETE http://jasper:8080/jasperserver/rest/resource/MOT >/dev/null 2>&1"
        },
        jasper_sync: {
            command: "$scripts_workspace/developer/jasper.sh ../jasperreports/ jasper root password localhost motdbuser password mot 1"
        },
        composer: {
            command: grunt.config.get('composerModules').dirs.map(function (dir) {
                return 'printf "\n>> Updating: \'' + dir + '\'\n" && cd ' + dir + ' && composer install && composer dump-autoload -o '
            }).join('; ')
        },
        expand_vagrant_puppet: {
            command: 'cd $WORKSPACE/../puppet-code && rm -Rf work && bash build vagrant'
        },
        api_token: {
            command: function () {
                var user = grunt.option('u');
                if(!user){
                    grunt.fail.warn('You must provide a username: e.g. --u=tester1 or you can add --force to use tester1 as default');
                    user = 'tester1';
                }
                var password = grunt.option('p') || 'Password1';

                console.log('Token for: ' + user);
                return 'curl -s "<%= url.openam %>/sso/identity/authenticate?username=' + user + '&password=' + password + '"' +
                    ' | sed -e "s/token.id=/Bearer /g"';
            }
        },
        special_notice_broadcast: {
            command: 'grunt dev:token --u=cron-job',
            options: {
                callback: function(err, stdout, stderr) {
                    var token = stdout.match(/Bearer (.*)/g);
                    if(token.length === 0) {
                        grunt.fail("Couldn't get access token from openam");
                    }
                    console.log('Broadcasting special notices. API output:');

                    var apiUrl = grunt.config.get('url.api');
                    var command = 'curl -s -H \'Content-Type: application/json\' -H "Authorization: ' + token[0] +
                        '"' +
                    ' -X POST "' + apiUrl + '/special-notice-broadcast"';
                    var snOutput = require('child_process').execSync(command).toString();

                    console.log(command);
                    console.log(snOutput);
                    if (snOutput.match(/errors/g)){
                        grunt.fatal("Failed to send special notices");
                    }
                }
            }
        },
        national_statistics_amazon_cache_clear: {
            command: function () {
                return 'curl -s ' + grunt.config.get('url.testsupport') + '/testsupport/clear-statistics-amazon-cache';
            }
        },
        update_all_java_services: {
            command: updateJavaService("all")
        },
        update_authorisation_service: {
            command: updateJavaService("authr")
        },
        update_vehicle_service: {
            command: updateJavaService("vehicle")
        },
        update_mottest_service: {
            command: updateJavaService("mot-test")
        },
        reset_database_anonymised: {
            command: resetDb("anonymised")
        },
        reset_database_10k: {
            command: resetDb("10k")
        }
    });
};
