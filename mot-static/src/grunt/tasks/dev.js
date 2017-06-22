/**
 * Add our dev tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function (grunt, config) {
        grunt.registerTask('dev:optimise', 'Switches the environment into optimised mode',
            [
                'sshexec:reset_database',
                'amazon:cache:clear:national-statistics',
                'sshexec:xdebug_disable',
                'sshexec:server_mod_prod',
                'doctrine:proxy',
                'apache:restart:all'
            ]
        );
        grunt.registerTask('dev:std', 'Switches the environment into standard development mode',
            [
                'apache:restart:all', // reset DB requires a clean class cache, hence reset happens twice
                'sshexec:reset_database',
                'amazon:cache:clear:national-statistics',
                'sshexec:server_mod_dev',
                'sshexec:doctrine_default_develop_dist',
                'apache:restart:all'
            ]
        );
        grunt.registerTask('dev:create_dvsa_logger_db', 'Creates the DVSA Logger database',
            [
                'sshexec:create_dvsa_logger_db'
            ]
        );
        grunt.registerTask('dev:mysql_general_log_enable', 'Enables mysql general log',
            [
                'sshexec:mysql_general_log_enable',
                'dev:restart:mysql'
            ]
        );
        grunt.registerTask('dev:mysql_general_log_disable', 'Disables mysql general log',
            [
                'sshexec:mysql_general_log_disable',
                'dev:restart:mysql'
            ]
        );
        grunt.registerTask('puppet_apply', 'Runs puppet apply for all VMs',
        [
            'shell:expand_vagrant_puppet',
            'sshexec:papply_dev',
            'sshexec:papply_dev2'
        ]);

        grunt.registerTask('dev:token',
            'Gets OpenAM token for given user (e.g. --u=tester1) and password (--p=password).',
            [
                'shell:api_token'
            ]
        );
        grunt.registerTask('special-notice:broadcast',
            'Forces special notice broadcast',
            [
                'shell:special_notice_broadcast'
            ]
        );
        grunt.registerTask('switch:branch', 'Runs common tasks after switching branches',
        [
            'apache:restart:all', // reset DB requires a clean class cache, hence reset happens twice
            'shell:composer',
            'sshexec:mysql_proc_fix',
            'sshexec:reset_database',
            'amazon:cache:clear:national-statistics',
            'sshexec:server_mod_dev',
            'sshexec:doctrine_default_develop_dist',
            'doctrine:proxy',
            'apache:restart:all'
        ]);

        grunt.registerTask('dev:restart:apache', 'Restarts Apache', 'apache:restart:all');
        grunt.registerTask('dev:restart:authorisation-service', 'Restarts authorisation service', 'sshexec:authorisation_service_restart');
        grunt.registerTask('dev:restart:openam', 'Restarts OpenAM', 'sshexec:openam_restart');
        grunt.registerTask('dev:restart:opendj', 'Restarts OpenDJ', 'sshexec:opendj_restart_dev');
        grunt.registerTask('dev:restart:jasper', 'Restarts Jasper', 'sshexec:jasper_restart');
        grunt.registerTask('dev:restart:mot-test', 'Restarts mot-test service', 'sshexec:mot_test_restart');
        grunt.registerTask('dev:restart:vehicle-service', 'Restarts vehicle service', 'sshexec:vehicle_service_restart');
        grunt.registerTask('dev:restart:mysql', 'Restarts Mysql', 'sshexec:mysql_restart_dev');
        grunt.registerTask('dev:restart:all', 'Restarts all known services', [
            'apache:restart:all',
            'sshexec:openam_restart',
            'sshexec:opendj_restart_dev',
            'sshexec:mysql_restart_dev',
            'sshexec:jasper_restart',
            'sshexec:authorisation_service_restart',
            'sshexec:mot_test_restart',
            'sshexec:vehicle_service_restart'
        ]);
        grunt.registerTask('dev:2fa_off', 'Sets 2fa toggle off', ['sshexec:ft2fa_off_dev', 'sshexec:ft2fa_off_dev2', 'sshexec:authorisation_service_restart', 'dev:restart:apache']);
        grunt.registerTask('dev:2fa_on', 'Sets 2fa toggle on', ['sshexec:ft2fa_on_dev', 'sshexec:ft2fa_on_dev2', 'sshexec:authorisation_service_restart','dev:restart:apache']);
        grunt.registerTask('dev:2fa_hardstop_off', 'Sets 2fa toggle off', ['sshexec:ft2fahardstop_off_dev', 'dev:restart:apache']);
        grunt.registerTask('dev:2fa_hardstop_on', 'Sets 2fa toggle on', ['sshexec:ft2fahardstop_on_dev', 'dev:restart:apache']);
        grunt.registerTask('dev:ft:enable', 'Enables toggle from --ft=[...] argument, e.g. dev:ft:enable --ft=new_homepage', [
            'sshexec:ft_enable_testsupport',
            'sshexec:ft_enable_frontend',
            'sshexec:ft_enable_api',
            'apache:restart'
        ]);
        grunt.registerTask('dev:ft:disable', 'Disables toggle from --ft=[...] argument, e.g. dev:ft:disable --ft=new_homepage',[
            'sshexec:ft_disable_testsupport',
            'sshexec:ft_disable_frontend',
            'sshexec:ft_disable_api',
            'apache:restart'
        ]);

        grunt.registerTask('amazon:cache:clear:national-statistics', 'Clears the Amazon S3 cache of national statistics for test quality information', [
            'shell:national_statistics_amazon_cache_clear'
        ]);
        grunt.registerTask('dev:zend-dev-tools:disable', 'Enable Zend Developer Tools', 'sshexec:zend_dev_tools_disable');
        grunt.registerTask('dev:zend-dev-tools:enable', 'Disable Zend Developer Tools', 'sshexec:zend_dev_tools_enable');

        grunt.registerTask('dev:update-service:all', 'Update all java services', 'shell:update_all_java_services');
        grunt.registerTask('dev:update-service:authorisation', 'Update authorisation service', 'shell:update_authorisation_service');
        grunt.registerTask('dev:update-service:mot-test', 'Update mot-test service', 'shell:update_mottest_service');
        grunt.registerTask('dev:update-service:vehicle', 'Update vehicle service', 'shell:update_vehicle_service');
};
