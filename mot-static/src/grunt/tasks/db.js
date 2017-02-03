/**
 * Add our db tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('db:reset', 'Reset the database on the VM with the small data set', 'sshexec:reset_database');
        grunt.registerTask('db:repair', 'Repair a broken mysql proc table in the VM', 'sshexec:mysql_proc_fix');
};