/**
 * WP-ERP HR module — React 19 admin build (module-scoped).
 *
 * Extends @wordpress/scripts default config. Single entry: `employees`.
 * Output: ./assets/dist-react/
 *
 * Isolated from the plugin-root webpack.config.js (which builds Settings + Accounting + i18n).
 * No alias / module / output overlap. See openspec/changes/redesign-hr-free/build-isolation.md.
 */

const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Patch the SVG rule emitted by `@wordpress/scripts` to drop the legacy
 * `url-loader` step. wp-scripts ships `use: ['@svgr/webpack', 'url-loader']`,
 * but `url-loader` pulls a nested `schema-utils@4` that throws against our
 * deduped `ajv@8` (see modules/hrm/package.json `overrides`). `@svgr/webpack`
 * alone is sufficient for SVG-as-React-component imports from TS/TSX files.
 */
const patchedModule = ( () => {
	const base = defaultConfig.module || { rules: [] };
	const rules = ( base.rules || [] ).map( ( rule ) => {
		if (
			rule &&
			rule.test &&
			rule.test.toString() === /\.svg$/.toString() &&
			rule.issuer &&
			rule.issuer.toString() === /\.(j|t)sx?$/.toString()
		) {
			return { ...rule, use: [ '@svgr/webpack' ] };
		}
		return rule;
	} );
	return { ...base, rules };
} )();

module.exports = {
	...defaultConfig,
	entry: {
		employees: path.resolve(
			__dirname,
			'assets/src-react/app/pages/employees.entry.tsx'
		),
		// Tiny globals exposer for pro STANDALONE pages (workflow / CFB) to share
		// this build's plugin-ui + react-router-dom (shares the same vendor.js).
		'hr-globals': path.resolve(
			__dirname,
			'assets/src-react/app/pages/hr-globals.entry.tsx'
		),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/dist-react' ),
		filename: '[name].js',
		clean: true,
	},
	resolve: {
		...defaultConfig.resolve,
		extensions: [ '.ts', '.tsx', '.js', '.jsx', '.json' ],
		alias: {
			...( defaultConfig.resolve && defaultConfig.resolve.alias ),
			'@': path.resolve( __dirname, 'assets/src-react' ),
		},
	},
	module: patchedModule,
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			...( defaultConfig.optimization && defaultConfig.optimization.splitChunks ),
			chunks: 'all',
			name: 'vendor',
		},
	},
};
