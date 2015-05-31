module.exports = {
	options: {
		livereload: true
	},

	config: {
		files: 'grunt/watch.js'
	},

	php: {
		files  : ['**/*.php'],
		tasks  : ['checktextdomain', 'phpunit'],
		options: {
			debounceDelay: 5000
		}
	}
}
