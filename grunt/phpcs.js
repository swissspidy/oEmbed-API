module.exports = {
	default: {
		cmd: 'vendor/bin/phpcs',
		args: [ '-n', '--report=emacs', '--standard=phpcs.ruleset.xml', 'classes' ]
	}
}
