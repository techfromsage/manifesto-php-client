module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    phpunit: {
      classes: {
        dir: 'test/unit'
      },
      options: {
        bin: 'vendor/bin/phpunit',
        configuration:'test/unit/phpunit.xml',
        colors: true,
        stopOnError:true,
        stopOnFailure:true,
        failOnFailures:true
      }
    },
    composer : {
      options : {
        usePhp: true,
        composerLocation: './composer.phar'
      }
    }
  });

  grunt.registerTask('test', ['phpunit']);
  grunt.registerTask('default', ['composer:install', 'phpunit']);
};
