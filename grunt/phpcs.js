module.exports = {
	default: {
		cmd: 'phpcs',
		args: [ '-n', '--report=emacs', '--standard=phpcs.ruleset.xml', 'classes' ]
	}
}
