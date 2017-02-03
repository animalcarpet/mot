
/**
 * Add our userSync tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
    grunt.registerTask('openam:refresh-users-db', 'Runs /vagrant/scripts/import-data.sh', 'sshexec:openam_refresh_users_db');
};