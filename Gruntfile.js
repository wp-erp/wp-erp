'use strict';
module.exports = function(grunt) {
    var pkg = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            images: 'assets/images',
            js: 'assets/js',
            less: 'assets/less',
            hrmJS: 'modules/hrm/assets/js/',
            crm: 'modules/crm/assets'
        },

        // Compile all .less files.
        less: {
            admin: {
                files: {
                    '<%= dirs.css %>/admin.css': '<%= dirs.less %>/admin/admin.less',
                    '<%= dirs.css %>/setup.css': '<%= dirs.less %>/admin/setup.less'
                }
            },

            frontend: {
                files: {
                    '<%= dirs.crm %>/css/erp-subscription-form.css': '<%= dirs.crm %>/less/erp-subscription-form.less',
                    '<%= dirs.crm %>/css/erp-subscription-edit.css': '<%= dirs.crm %>/less/erp-subscription-edit.less'
                }
            }
        },

        uglify: {
            minify: {
                files: {
                    '<%= dirs.js %>/erp.min.js': ['<%= dirs.js %>/erp.js'],
                    '<%= dirs.js %>/jquery-popup.min.js': ['<%= dirs.js %>/jquery-popup.js'],
                    '<%= dirs.js %>/settings.min.js': ['<%= dirs.js %>/settings.js'],
                    '<%= dirs.js %>/upload.min.js': ['<%= dirs.js %>/upload.js'],
                    '<%= dirs.js %>/system-status.min.js': ['<%= dirs.js %>/system-status.js'],
                    '<%= dirs.js %>/erp-all.min.js': [
                        '<%= dirs.js %>/erp.min.js',
                        '<%= dirs.js %>/jquery-popup.min.js',
                        '<%= dirs.js %>/settings.min.js',
                        '<%= dirs.js %>/upload.min.js'
                    ],
                    '<%= dirs.hrmJS %>/hrm.min.js': ['<%= dirs.hrmJS %>/hrm.js'],
                    '<%= dirs.hrmJS %>/leave.min.js': ['<%= dirs.hrmJS %>/leave.js']
                }
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: [
                'Gruntfile.js',
                '<%= dirs.js %>/*.js',
                '!<%= dirs.js %>/*.min.js'
            ]
        },

        watch: {
            less: {
                files: [
                    '<%= dirs.less %>/*.less',
                    '<%= dirs.less %>/admin/*.less',
                    '<%= dirs.crm %>/less/erp-subscription-form.less',
                    '<%= dirs.crm %>/less/erp-subscription-edit.less'
                ],

                tasks: ['less:admin', 'less:frontend']
            },

            js: {
                files: [
                    '<%= dirs.js %>/*',
                    '<%= dirs.hrmJS %>/*'
                ],
                tasks: ['uglify']
            }
        },

        // Clean up build directory
        clean: {
            main: ['build/']
        },

        // Copy the plugin into the build directory
        copy: {
            main: {
                src: [
                    '**',
                    '!node_modules/**',
                    '!.codekit-cache/**',
                    '!.idea/**',
                    '!build/**',
                    '!bin/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!package-lock.json',
                    '!composer.json',
                    '!composer.lock',
                    '!debug.log',
                    '!phpunit.xml',
                    '!.gitignore',
                    '!.gitmodules',
                    '!npm-debug.log',
                    '!plugin-deploy.sh',
                    '!export.sh',
                    '!config.codekit',
                    '!nbproject/*',
                    '!assets/less/**',
                    '!tests/**',
                    '!README.md',
                    '!CONTRIBUTING.md',
                    '!**/*~',
                    '!phpcs.xml',
                    '!.env',
                    '!.env.example',
                    '!codeception.yml',
                    '!eslintrc.js',
                    '!webpack.config.js',
                    '!modules/accounting/assets/less/**',
                    '!modules/accounting/assets/src/**',
                    '!vendor/google/apiclient-services/src/Google/Service/**',
                    'vendor/google/apiclient-services/src/Google/Service/Gmail.php',
                    'vendor/google/apiclient-services/src/Google/Service/Gmail/**'
                ],
                dest: 'build/'
            }
        },

        // Compress build directory into <name>.zip and <name>-<version>.zip
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/erp-v' + pkg.version + '.zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'erp'
            }
        },

        run: {
            options: {},

            reset: {
                cmd: 'npm',
                args: ['run', 'build']
            },

            makepot: {
                cmd: 'npm',
                args: ['run', 'makepot']
            },

            removeDev: {
                cmd: 'composer',
                args: ['install', '--no-dev']
            },

            dumpautoload: {
                cmd: 'composer',
                args: ['dump-autoload', '-o']
            },

            composerInstall: {
                cmd: 'composer',
                args: ['install']
            }
        }

    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-run');

    grunt.registerTask('default', [
        'less', 'uglify'
    ]);

    grunt.registerTask('release', [
        'uglify'
    ]);

    grunt.registerTask('zip', [
        'clean',
        'run:reset',
        'run:makepot',
        'run:removeDev',
        'run:dumpautoload',
        'copy',
        'compress',
        'run:composerInstall',
        'run:dumpautoload'
    ]);
};
