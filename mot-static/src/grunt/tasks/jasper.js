/**
 * Add Jasper tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
        grunt.registerTask(
            'jasper:sync',
            'Reset The jasper template with the new one',
            ['shell:jasper_remove', 'shell:jasper_sync']
        );

        /**
         * Sometimes vagrant up jasper doesn't always leave the Tomcat6 server
         * running, this command will start it up manually.
         */
	grunt.registerTask(
            'jasper:kick',
            'Ensures that the Tomcat6 server is actually running',
            [
                'sshexec:jasper_tomcat_restart'
            ]
        );
};
