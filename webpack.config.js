/**
 * Wordpress dependencies
 */
const { getAsBooleanFromENV } = require( '@wordpress/scripts/utils' );
/**
 * External dependencies
 */
const path = require( 'path' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
// const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
/**
 * Check if the --experimental-modules flag is set.
 */
const hasExperimentalModulesFlag = getAsBooleanFromENV(
	'WP_EXPERIMENTAL_MODULES'
);
/**
 * Get default script config from @wordpress/scripts
 * based on the --experimental-modules flag.
 */
const defaultConfigs = hasExperimentalModulesFlag
	? require( '@wordpress/scripts/config/webpack.config' )
	: [ require( '@wordpress/scripts/config/webpack.config' ) ];
const [ scriptConfig ] = defaultConfigs;
/**
 * Filter plugins from the default config
 */
const plugins = scriptConfig.plugins.filter( ( item ) => {
	return ! [ 'CopyPlugin' ].includes(
		item.constructor.name
	);
} );

/**
 * Webpack configuration
 */
const assetConfig = {
	...scriptConfig,
	entry: {
		'style': [
			path.resolve( __dirname, 'src', 'index.scss' ),
		],
		editor: [
			path.resolve( __dirname, 'src', 'editor.ts' ),
		]
	},
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name].js',
		clean: false,
	},
	resolve: {
		...scriptConfig.resolve,
		alias: {
			'@images': path.resolve( __dirname, 'src/images' ),
		},
	},
	plugins: [
		...plugins,
		new RemoveEmptyScriptsPlugin(),
	],
};

module.exports = () => {
	return [ ...defaultConfigs, assetConfig ];
};
