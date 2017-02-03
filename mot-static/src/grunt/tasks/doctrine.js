/**
 * Add our doctrine tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('doctrine:default', 'Reset the local doctrine file to default settings', ['sshexec:doctrine_default_local_dist', 'sshexec:apache_restart']);
        grunt.registerTask('doctrine:optimise', 'Reset the local doctrine file to default settings', ['sshexec:doctrine_optimised_local_dist', 'sshexec:apache_restart']);
        grunt.registerTask('doctrine:proxy', 'Generates doctrine proxies', ['sshexec:delete_doctrine_cache_folders', 'sshexec:doctrine_proxy_gen']);
};