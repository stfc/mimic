module.exports = function(grunt) {

  grunt.initConfig({
    bower_concat: {
      all: {
        dest: 'js/bower.js'
      }
    },
    uglify: {
      bower: {
        options: {
          mangle: true,
          compress: true
        },
        files: {
          'js/bower.min.js': 'js/bower.js'
        }
      }
    }

  });
  grunt.registerTask('buildbower', [
    'bower_concat',
    'uglify:bower'
  ]);
  require('load-grunt-tasks')(grunt);
};
