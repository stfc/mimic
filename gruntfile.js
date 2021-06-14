module.exports = function(grunt) {
    grunt.initConfig({
        concat: {
            main: {
                options: {
                    separator: ';',
                },
                src: [
                    'assets/dist/js/bower.js',
                    'assets/js/key.js',
                    'assets/js/plugins.js',
                    'assets/js/tooltip.js',
                    'bower_components/masonry/dist/masonry.pkgd.js'
                ],
                dest: 'assets/dist/js/script.js'
            },
            node: {
                options: {
                    separator: ';',
                },
                src: [
                    'bower_components/jquery-ui/jquery-ui.min.js'
                ],
                dest: 'assets/dist/js/node-page.js'
            },
            css: {
                src: [
                    'assets/css/style.css',
                    'assets/css/tooltip.css',
                    'bower_components/jquery.cookiebar/jquery.cookiebar.css'
                ],
                dest: 'assets/dist/css/style.css',
            }
        },
        uglify: {
            options: {
                mangle: false
            },
            js: {
                files: {
                    'assets/dist/js/script.min.js': ['assets/dist/js/script.js']
                }
            }
        },
        cssmin: {
            main: {
                files: [{
                    src: 'assets/dist/css/style.css',
                    dest: 'assets/dist/css/style.min.css',
                }]
            },
            info: {
                files: [{
                    src: 'assets/css/info.css',
                    dest: 'assets/dist/css/info-style.min.css',
                }]
            }
        },
        copy: {
            images: {
                cwd: 'assets/images/',
                src: '**/*',
                dest: 'assets/dist/images/',
                expand: true
            },
            fonts: {
                cwd: 'assets/fonts/',
                src: '**/*',
                dest: 'assets/dist/fonts/',
                expand: true
            }
        }
    });
    grunt.registerTask('buildmimic', [
        'concat',
        'uglify',
        'cssmin',
        'copy'
    ]);
    grunt.registerTask('buildmimiccss', [
        'concat:css',
        'cssmin'
    ]);
    require('load-grunt-tasks')(grunt);
};
//
