module.exports = function(grunt, config) {
    grunt.config('mysql_config', {
        user: 'motdbuser',
        password: 'password',
        host: 'localhost',
        database: 'mot2',
        grantuser: 'motdbuser'
    });
};
