/* jshint node:true */
'use strict';
module.exports = function(grunt) {
	'use strict';

	// Project configuration
	var localenv = grunt.file.readJSON( 'localenv.json' );
	var localconfig = grunt.file.readJSON( 'config.json' );
	var localpkg = grunt.file.readJSON( 'package.json' );


	var grunt_config = {
		pkg: localpkg,
		cfg: localconfig,
		env: localenv,
	};

	grunt.initConfig( grunt_config );

	// load modules & tasks
	grunt.loadTasks("tasks");

	// Default task.
	grunt.registerTask( 'default', ['build', 'bs-init', 'watch'] );
	grunt.registerTask( 'bs',      [ 'bs-init', 'watch'] );

	// internationalization
	grunt.registerTask( 'i18n',    ['checktextdomain', 'makepot'] );

	// compiles all files for dev
	grunt.registerTask( 'build',   ['notify', 'i18n'] );
	grunt.registerTask( 'dev',     ['build'] );
	// compiles all files for staging/production
	grunt.registerTask( 'prod',    ['build'] );
};
