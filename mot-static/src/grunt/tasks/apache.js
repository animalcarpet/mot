/**
 * Add our apache tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('apache:restart', 'See apache:restart:all', 'apache:restart:all');
        grunt.registerTask('apache:restart:frontend', 'Restart the apache server on the dev VM', 'sshexec:apache_restart_dev');
        grunt.registerTask('apache:restart:api', 'Restart the apache server on the dev2 VM', 'sshexec:apache_restart_dev2');
        grunt.registerTask('apache:restart:all', 'Restart the apache server on both dev and dev2 VMs', [
            'sshexec:apache_restart_dev',
            'sshexec:apache_restart_dev2'
        ]);
        grunt.registerTask('apache:clear-php-sessions', 'Removes PHP sessions in the VM', [
            'sshexec:apache_clear_php_sessions_dev',
            'sshexec:apache_clear_php_sessions_dev2'
        ]);
};
