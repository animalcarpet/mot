
module.exports = function(grunt, config) {
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
        install_devtools: {
            command: "./Jenkins_Scripts/install_devtools.sh"
        },
        env_dvsa_update_check: {
            command: '$scripts_workspace/env/dvsa_update_check.sh'
        },
        env_dvsa_hotfix: {
            command: '$scripts_workspace/env/dvsa_hotfix.sh'
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
                    var execSync = require('exec-sync');
                    var snOutput = execSync('curl -s -H \'Content-Type: application/json\' -H "Authorization: ' + token[0] +
                        '"' +
                    ' -X POST "' + apiUrl + '/special-notice-broadcast"');
                    console.log(snOutput);

                }
            }
        },
        national_statistics_amazon_cache_clear: {
            command: function () {
                return 'curl -s ' + grunt.config.get('url.testsupport') + '/testsupport/clear-statistics-amazon-cache';
            }
        }
    });
};
