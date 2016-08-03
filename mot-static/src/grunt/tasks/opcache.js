module.exports = function(grunt) {
	grunt.registerTask(
		'opcache:unload',
		'Prevent opcache extension being loaded',
		[
			'sshexec:opcache_unload_dev',
			'sshexec:opcache_unload_dev2',
			'apache:restart:all'
		]);

	grunt.registerTask(
		'opcache:load',
		'Enables opcache extension',
		[
			'sshexec:opcache_load_dev',
			'sshexec:opcache_load_dev2',
			'apache:restart:all'
		]);
};
