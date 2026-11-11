/**
 * Ambient module declaration for SVG imports.
 *
 * wp-scripts default webpack config routes `*.svg` imported from a TS/TSX
 * file through `@svgr/webpack` + `url-loader`, returning a React component
 * as the default export. This file MUST stay a script (no top-level
 * imports/exports) so the module-pattern declaration is ambient.
 */

declare module '*.svg' {
	const Component: React.FunctionComponent< React.SVGProps< SVGSVGElement > >;
	export default Component;
}
