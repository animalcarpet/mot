module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
    var mot = require('./mot-static/src/grunt/mot-grunt.js');

    grunt.initConfig({
        pkg:                grunt.file.readJSON('package.json'),
        buildId:            grunt.template.today("yyyymmddhhmmss"),
        buildDateTime:      grunt.template.today("dddd, mmmm dS, yyyy, h:MM:ss TT"),
        ENV_DEVELOPMENT:    'development',
        ENV_PRODUCTION:     'production',
        environment:        'development',
        legacy:             false
    });

    mot.initialiseGrunt(grunt);
};
