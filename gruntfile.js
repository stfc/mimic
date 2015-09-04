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
          'min/js/bower.min.js': 'js/bower.js'
        }
      }
    },
    cssmin: {
      target: {
        files: [{
          expand: true,
          cwd: 'css',
          src: ['*.css', '!*.min.css'],
          dest: 'min/css',
          ext: '.min.css'
        }]
      }
    }

  });
  grunt.registerTask('buildbower', [
    'bower_concat',
    'uglify:bower',
    'cssmin:target'
  ]);
  require('load-grunt-tasks')(grunt);
};
