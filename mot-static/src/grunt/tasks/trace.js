/**
 * Add our trace tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
    if (config.environment === config.ENV_DEVELOPMENT) {
        grunt.registerTask('trace:api', 'Trace the API logs', 'sshexec:trace_api_log');
        grunt.registerTask('trace:api:access', 'Trace the API apache access log', 'sshexec:trace_api_access_log');
        grunt.registerTask('trace:api:error', 'Trace the API apache error log', 'sshexec:trace_api_error_log');
        grunt.registerTask('trace:api:system', 'Trace the API system logs', 'sshexec:trace_api_system_log');
        grunt.registerTask('trace:mysql', 'Trace the mysql general log', 'sshexec:trace_mysql_log');
        grunt.registerTask('trace:web', 'Trace the web logs', 'sshexec:trace_web_log');
        grunt.registerTask('trace:web:access', 'Trace the web apache access logs', 'sshexec:trace_frontend_access_log');
        grunt.registerTask('trace:web:error', 'Trace the web apache error logs', 'sshexec:trace_frontend_error_log');
        grunt.registerTask('trace:web:system', 'Trace the web system logs', 'sshexec:trace_frontend_system_log');
        grunt.registerTask('trace:testsupport:access', 'Trace the testsupport access log', 'sshexec:trace_testsupport_access_log');
        grunt.registerTask('trace:testsupport:error', 'Trace the testsupport error log', 'sshexec:trace_testsupport_error_log');
        grunt.registerTask('trace:jasper:access', 'Trace the jasper access log', 'sshexec:trace_jasper_access_log');
        grunt.registerTask('trace:vehicle-service:access', 'Trace the vehicle service access log', 'sshexec:trace_vehicle_service_access_log');
        grunt.registerTask('trace:vehicle-service:error', 'Trace the vehicle service error log', 'sshexec:trace_vehicle_service_error_log');
    }
};