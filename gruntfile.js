module.exports = function(grunt) {
  grunt.initConfig({
    bower_concat: {
      all: {
        dest: 'js/bower.js'
      }
    },
    uglify: {
      js: {
        options: {
          mangle: true,
          compress: true
        },
        files: [{
          expand: true,
          cwd: 'src/js',
          src: ['*.js', '!*.min.js'],
          dest: 'js'
        }]
      }
    },
    cssmin: {
      css: {
        files: [{
          expand: true,
          cwd: 'src/css',
          src: ['*.css', '!*.min.css'],
          dest: 'css'
        }]
      }
    }
  });
  grunt.registerTask('buildbower', [
    'bower_concat',
    'uglify:js',
    'cssmin:css'
  ]);
  require('load-grunt-tasks')(grunt);
};
