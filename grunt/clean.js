module.exports = {
	release      : [
		'release/<%= package.version %>/',
		'release/svn/'
	],
	svn_readme_md: [
		'release/svn/readme.md'
	]
}
