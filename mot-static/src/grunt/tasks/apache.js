/**
 * Add our apache tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
    if (config.environment === config.ENV_DEVELOPMENT) {
        grunt.registerTask('apache:restart', 'Restart the apache server in the VM', 'sshexec:apache_restart');
        grunt.registerTask('apache:clear-php-sessions', 'Removes PHP sessions in the VM', 'sshexec:apache_clear_php_sessions');
    }
};