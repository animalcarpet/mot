/**
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask('password-policy:show', 'Show MOT password policy.', 'sshexec:password_policy_show');
        grunt.registerTask('password-policy:list', 'Show list of all OpenAM password policies.', 'sshexec:password_policy_list');
        grunt.registerTask('password-policy:delete', 'Deletes MOT password policy.', 'sshexec:password_policy_delete');
        grunt.registerTask('password-policy:create', 'Creates MOT password policy.', 'sshexec:password_policy_create');
};
