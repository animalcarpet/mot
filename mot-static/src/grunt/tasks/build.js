/**
 * Add our test tasks into grunt
 *
 * @param grunt
 * @param config
 */
module.exports = function(grunt, config) {
    grunt.registerTask('build:composer', 'Download all the composer dependencies for each module', ['shell:composer']);
};
