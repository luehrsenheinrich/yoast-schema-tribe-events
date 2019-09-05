// Include prompt module.
const prompt = require( 'prompt' );
const isEmpty = require( 'lodash/isEmpty' );
const forEach = require( 'lodash/forEach' );
const replace = require( 'replace-in-file' );

// This json object is used to configure what data will be retrieved from command line.
var prompt_attributes = [
	{
		name: 'oldSlug',
		type: 'string',
		hidden: false,
		default: 'wpmyte',
	},
	{
		name: 'newSlug',
		type: 'string',
		hidden: false,
	},
];

// Start the prompt to read user input.
prompt.start();

// Prompt and get user input then display those data in console.
prompt.get( prompt_attributes, function ( err, result ) {
	if ( err ) {
		console.log( err );
		return 1;
	} else {
		// Get user input from result object.
		const oldSlug = result.oldSlug;
		const newSlug = result.newSlug;
		const fallbackSlug = oldSlug.startsWith( '_' ) ? `bb${ oldSlug.substring( 1 ) }` : false;

		let from = [ new RegExp( oldSlug, 'g' ) ];
		let fromCaps = [ new RegExp( oldSlug.toUpperCase(), 'g' ) ];

		if ( fallbackSlug ) {
			from.push( new RegExp( fallbackSlug, 'g' ) );
		}

		let replaceMap = {};
		replaceMap[ oldSlug ] = newSlug;

		if ( fallbackSlug ) {
			replaceMap[ fallbackSlug ] = newSlug;
		}

		const dir = __dirname;
		const dirBase = dir.substring( 0, dir.length - 4 );

		const options = {
			files: dirBase + '/**/*.*',
			from: from,
			to: newSlug,
			ignore: [ '**/node_modules/**' ]
		};

		const optionsCaps = {
			files: dirBase + '/**/*.*',
			from: fromCaps,
			to: newSlug.toUpperCase(),
			ignore: [ '**/node_modules/**' ]
		};

		const results = replace.sync( options );
		const resultsCaps = replace.sync( optionsCaps );

		forEach( resultsCaps, (result) => {
			if ( result.hasChanged ) {
				console.log( 'changed: ', result.file.replace( dirBase, '' ) );
			}
		});

		forEach( results, (result) => {
			if ( result.hasChanged ) {
				console.log( 'changed: ', result.file.replace( dirBase, '' ) );
			}
		});

	}
});
