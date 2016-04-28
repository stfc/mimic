module.exports = function(grunt) {
    grunt.initConfig({
        bower_concat: {
            all: {
                dest: 'assets/dist/js/bower.js',
                exclude: [
                    'jquery-ui',
                    'masonry'
                ],
                mainFiles: {
                    'outlayer': [
                        'bower_components/outlayer/item.js',
                        'bower_components/outlayer/outlayer.js'
                    ]
                }
            }
        },
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
        }
    });
    grunt.registerTask('buildmimic', [
        'bower_concat',
        'concat',
        'uglify',
        'cssmin'
    ]);
    grunt.registerTask('buildmimiccss', [
        'concat:css',
        'cssmin'
    ]);
    require('load-grunt-tasks')(grunt);
};
//
