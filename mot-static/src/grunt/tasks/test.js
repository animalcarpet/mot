/**
 * Add our test tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('test:php', 'Test PHP via phpunit within the VM', ['test:php:api','test:php:api:db:verification','test:php:common','test:php:frontend']);
        grunt.registerTask('test:coverage', 'Test PHP via phpunit within the VM and generate coverage', 'sshexec:phpunit_coverage');
        grunt.registerTask('test:php:frontend', 'Runs phpunit on the web-frontend tier', 'sshexec:test_php_frontend');
        grunt.registerTask('test:php:api', 'Runs phpunit on the api tier', 'sshexec:test_php_api');
        grunt.registerTask('test:php:api:db:verification', 'Runs phpunit for db-verification on the api', 'sshexec:test_php_api_db_verification');
        grunt.registerTask('test:php:common', 'Runs phpunit on the common web modules', 'sshexec:test_php_common');
        grunt.registerTask('test:behat', 'Runs Behat tests within the VM', 'sshexec:test_behat');
};