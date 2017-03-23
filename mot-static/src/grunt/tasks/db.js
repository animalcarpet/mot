/**
 * Add our db tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('db:reset', 'Reset the database with the small data set', 'sshexec:reset_database');
        grunt.registerTask('db:reset:anonymised', 'Reset the database with the anonymised data set', 'shell:reset_database_anonymised');
        grunt.registerTask('db:reset:10k', 'Reset the database with the 10k data set', 'shell:reset_database_10k');
        grunt.registerTask('db:repair', 'Repair a broken mysql proc table in the VM', 'sshexec:mysql_proc_fix');
};