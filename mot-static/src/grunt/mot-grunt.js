'use strict';

/**
 * Allows environment aware loading of grunt configuration(s)
 *
 * Dynamically loads all configs in the grunt/config folder
 *
 * @param grunt
 * @param path
 */
var loadFiles = function(grunt, path) {
    var config = grunt.config.get();
    grunt.file.recurse(path, function(abspath, rootdir, subdir, filename) {
        require(path+filename)(grunt, config);
    });
};

/**
 * Provide a public API to initialize out split grunt files into grunt
 *
 * @type {{initialiseGrunt: initialiseGrunt}}
 */
module.exports = {
    initialiseGrunt: function(grunt) {
        loadFiles(grunt, __dirname+'/config/');
        loadFiles(grunt, __dirname+'/tasks/');
    }
};
