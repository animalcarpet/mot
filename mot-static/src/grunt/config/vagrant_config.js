module.exports = function (grunt, config) {

    if (config.environment === config.ENV_DEVELOPMENT) {

        var execSync = require('exec-sync');
        var util = require('util');

        var workspace = process.env['dvsa_workspace'];
        if (!workspace){
            grunt.fatal('You must define "dvsa_workspace" enviroment variable (usually it\'s "~/MOTDEV")');
        }

        // Wrapper: If a VM is unresponsive, answers "" instead of blowing out grunt.
        var getKeyCmd = function (vmName) {
            var cacheFile = './.vagrant_ssh_config_cache/vagrant-ssh-config-' + vmName + '.local.cached';
            if (grunt.file.exists(cacheFile)) {
                return grunt.file.readJSON(cacheFile).privateKey;
            }
            var file = execSync(
                util.format(
                    'cd %s && vagrant ssh-config %s | grep IdentityFile | awk \'{print $2}\'' ,
                    workspace + '/mot-vagrant',
                    vmName
                )
            );
            var result = (file.length > 0) ? grunt.file.read(file) : '';
            grunt.file.write(cacheFile, JSON.stringify({privateKey: result}));
            return result;
        };

        grunt.config('service_config', {
            //workspace:  '/home/vagrant/mot',
            //phpRootDir: '/opt/rh/php55/root',
            httpdServiceName: 'httpd24-httpd',
            opendjServiceName: 'opendj',
            mysqlServiceName: 'mysql',
            jasperServiceName: 'tomcat',
            authrServiceName: 'authorisation-service'
        });

        // dev (Frontend, OpenDJ, Jasper, MySQL, Distauth)
        grunt.config('dev_config', {
            host: '10.10.10.30',
            port: 22,
            username: 'vagrant',
            privateKey: getKeyCmd('dev'),
            workspace: '/home/vagrant/mot',
            mysqlConfigDir: ''
        });
        // dev2 (API, OpenAM)
        grunt.config('dev2_config', {
            host: '10.10.10.50',
            port: 22,
            username: 'vagrant',
            privateKey: getKeyCmd('dev2'),
            workspace: '/home/vagrant/mot',
            phpRootDir: '/opt/rh/php55/root'
        });

        // dev2 (API, OpenAM)
        grunt.config('vagrant_config', grunt.config.get('dev2_config'));
        // dev (Frontend, OpenDJ, Jasper, MySQL)
        grunt.config('jasper_config', grunt.config.get('dev_config'));
        // dev2 (API, OpenAM)
        grunt.config('devopenam_config', grunt.config.get('dev2_config'));

    }
};
