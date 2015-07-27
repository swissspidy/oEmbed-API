module.exports = {
	dist: {
		options: {
			cwd            : '',
			domainPath     : '/languages',
			exclude        : [],
			include        : [],
			mainFile       : 'wp-api-oembed.php',
			potComments    : '',
			potFilename    : 'oembed-api.pot',
			potHeaders     : {
				poedit                 : true,
				'x-poedit-keywordslist': true,
				'report-msgid-bugs-to' : 'https://pascalbirchler.com',
				'last-translator'      : 'Pascal Birchler',
				'language-team'        : 'Pascal Birchler <pascal@required.ch>',
				'x-poedit-country'     : 'Switzerland'
			},
			processPot     : null,
			type           : 'wp-plugin',
			updateTimestamp: false
		}
	}
}
