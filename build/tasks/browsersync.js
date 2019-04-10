module.exports = function(grunt) {
	// Init BrowserSync manually

	var browserSync = require('browser-sync');
	grunt.registerTask('bs-init', function () {
//		var done = this.async();
		browserSync({
//			https:      false,
			open:       'external',
			watchTask:  true,
			logPrefix:  grunt.template.process('<%= pkg.name %>'),
			timestamps: true,
			proxy:      {
				target: grunt.template.process('<%= env.localurl %>')
			}
		}, function (err, bs) {

		});
	});
	// Inject CSS files to the browser
	grunt.registerTask('bs-inject-css', function () {
		browserSync.reload( '*.css' );
	});
	// Reload browser
	grunt.registerTask('bs-reload', function () {
		browserSync.reload([
			'dist/js/<%= pkg.name %>.js',
			'./**/*.php'
		]);
	});
};
