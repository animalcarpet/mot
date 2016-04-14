/**
 * Add our dev tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function (grunt, config) {
    if (config.environment === config.ENV_DEVELOPMENT) {

        grunt.registerTask('dev:optimise', 'Switches the environment into optimised mode',
            [
                'sshexec:reset_database',
                'sshexec:xdebug_disable',
                'sshexec:doctrine_optimised_develop_dist',
                'sshexec:server_mod_prod',
                'doctrine:proxy',
               'apache:restart:all'
            ]
        );
        grunt.registerTask('dev:std', 'Switches the environment into standard development mode',
            [
                'sshexec:fix_db_configs',
                'apache:restart:all', // reset DB requires a clean class cache, hence reset happens twice
                'sshexec:reset_database',
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
        grunt.registerTask('dev:dvsa_logger_enable', 'Enables the DVSA Logger',
            [
                'sshexec:enable_dvsa_logger_api',
                'sshexec:enable_dvsa_logger_web',
                'apache:restart:all'
            ]
        );
        grunt.registerTask('dev:dvsa_logger_disable', 'Disables the DVSA Logger',
            [
                'sshexec:disable_dvsa_logger_api',
                'sshexec:disable_dvsa_logger_web',
                'apache:restart:all'
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

        // Environment Maintenance Tasks
        grunt.registerTask('env:mot:updatecheck', 'Disables the DVSA Logger', [
            'shell:env_dvsa_update_check'
        ]);
        grunt.registerTask('env:mot:hotfix', 'Disables the DVSA Logger', [
            'shell:env_dvsa_hotfix'
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
            'shell:config_reload',
            'sshexec:fix_db_configs',
            'sshexec:mysql_proc_fix',
            'sshexec:reset_database',
            'sshexec:server_mod_dev',
            'sshexec:doctrine_default_develop_dist',
            'doctrine:proxy',
            'apache:restart:all'
        ]);

        grunt.registerTask('dev:restart:apache', 'Restarts Apache', [
            'apache:restart:all'
        ]);
        grunt.registerTask('dev:restart:opendj', 'Restarts OpenDJ', [
            'sshexec:opendj_restart_dev'
        ]);
        grunt.registerTask('dev:restart:jasper', 'Restarts Jasper', [
            'sshexec:jasper_restart'
        ]);
        grunt.registerTask('dev:restart:mysql', 'Restarts Mysql', [
            'sshexec:mysql_restart_dev'
        ]);
        grunt.registerTask('dev:restart:all', 'Restarts all known services', [
            'apache:restart:all',
            'sshexec:opendj_restart_dev',
            'sshexec:mysql_restart_dev',
            'sshexec:jasper_restart'
        ]);
    }
};