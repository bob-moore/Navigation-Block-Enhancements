/**
 * Wordpress dependencies
 */
const { getAsBooleanFromENV } = require( '@wordpress/scripts/utils' );
/**
 * External dependencies
 */
const path = require( 'path' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
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
// const plugins = scriptConfig.plugins.filter( ( item ) => {
// 	return ! [ 'MiniCssExtractPlugin' ].includes( item.constructor.name );
// } );

/**
 * Webpack configuration
 */
const assetConfig = {
	...scriptConfig,
	entry: {
		index: path.resolve( __dirname, 'src', 'style.scss' ),
	},
	plugins: [
		...scriptConfig.plugins,
		new RemoveEmptyScriptsPlugin()
	]
};

module.exports = () => {
	return [ ...defaultConfigs, assetConfig ];
};
