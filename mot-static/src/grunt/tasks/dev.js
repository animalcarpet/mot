/**
 * Add our dev tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function (grunt, config) {
    if (config.environment === config.ENV_DEVELOPMENT) {
        function isTaskForAws(){
            return config.legacy === false;
        }
        grunt.registerTask('dev:optimise', 'Switches the environment into optimised mode',
            [
                'sshexec:reset_database',
                'sshexec:xdebug_disable',
                'sshexec:doctrine_optimised_develop_dist',
                'sshexec:server_mod_prod',
                'doctrine:proxy',
                isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart'
            ]
        );
        grunt.registerTask('dev:std', 'Switches the environment into standard development mode',
            [
                isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart', // reset DB requires a clean class cache, hence reset happens twice
                'sshexec:reset_database',
                'sshexec:server_mod_dev',
                'sshexec:doctrine_default_develop_dist',
                isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart'
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
                isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart'
            ]
        );
        grunt.registerTask('dev:dvsa_logger_disable', 'Disables the DVSA Logger',
            [
                'sshexec:disable_dvsa_logger_api',
                'sshexec:disable_dvsa_logger_web',
                isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart'
            ]
        );

        // Environment Maintenance Tasks
        grunt.registerTask('env:mot:updatecheck', 'Disables the DVSA Logger', [
            'shell:env_dvsa_update_check'
        ]);
        grunt.registerTask('env:mot:hotfix', 'Disables the DVSA Logger', [
            'shell:env_dvsa_hotfix'
        ]);
        grunt.registerTask('switch:branch', 'Runs common tasks after switching branches',
        [
            isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart', // reset DB requires a clean class cache, hence reset happens twice
            'shell:composer',
            'shell:config_reload',
            'sshexec:mysql_proc_fix',
            'sshexec:reset_database',
            'sshexec:server_mod_dev',
            'sshexec:doctrine_default_develop_dist',
            'doctrine:proxy',
            isTaskForAws() ? 'apache:restart:all' : 'sshexec:apache_restart'
        ]);
    }
};