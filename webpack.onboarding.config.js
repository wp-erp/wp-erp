const webpack = require('webpack');
const path = require('path');
const postcss = require('postcss');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const OptimizeCSSPlugin = require('optimize-css-assets-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

// Scope all CSS selectors under #wperp-onboarding-root to prevent
// styles from leaking into WordPress admin or any other page.
const SCOPE = '#wperp-onboarding-root';
const scopeOnboardingCss = postcss.plugin('scope-onboarding-css', function() {
    return function(root) {
        root.walkRules(function(rule) {
            if (rule.parent && rule.parent.type === 'atrule' && rule.parent.name === 'keyframes') {
                return;
            }
            rule.selectors = rule.selectors.map(function(selector) {
                const s = selector.trim();
                if (s.startsWith(SCOPE) || s === ':root') return s;
                if (s === 'html' || s === 'body') return SCOPE;
                return SCOPE + ' ' + s;
            });
        });
    };
});

const isProduction = process.env.WEBPACK_ENV === 'production';

// Create separate extract instances for isolation
const extractCss = new ExtractTextPlugin({
    filename: '[name].css',
});

const config = {
    entry: {
        'onboarding': './includes/Admin/Onboarding/assets/src/index.jsx'
    },

    output: {
        path: path.resolve(__dirname, './includes/Admin/Onboarding/assets/dist'),
        filename: '[name].js',
        chunkFilename: '[name].[chunkhash].js',
        jsonpFunction: 'wpErpOnboardingWebpack'
    },

    resolve: {
        extensions: ['.js', '.jsx', '.json'],
        alias: {
            '@onboarding': path.resolve(__dirname, './includes/Admin/Onboarding/assets/src'),
        }
    },

    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                targets: {
                                    browsers: ['> 1%', 'last 2 versions', 'not dead']
                                },
                                useBuiltIns: 'usage',
                                corejs: 2
                            }],
                            ['@babel/preset-react', {
                                runtime: 'automatic'
                            }]
                        ],
                        plugins: [
                            '@babel/plugin-proposal-class-properties',
                            '@babel/plugin-proposal-object-rest-spread',
                            ['@babel/plugin-transform-runtime', {
                                corejs: 2,
                                helpers: true,
                                regenerator: true,
                            }]
                        ]
                    }
                }
            },
            {
                test: /\.css$/,
                use: extractCss.extract({
                    fallback: 'style-loader',
                    use: [
                        'css-loader',
                        {
                            loader: 'postcss-loader',
                            options: {
                                plugins: function() {
                                    return [
                                        require('tailwindcss'),
                                        require('autoprefixer'),
                                        scopeOnboardingCss,
                                    ];
                                }
                            }
                        }
                    ]
                })
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                use: [{
                    loader: 'file-loader',
                    options: {
                        name: 'images/[name].[ext]',
                        publicPath: (url) => {
                            // Return relative path from dist folder
                            return url;
                        }
                    }
                }]
            },
        ]
    },

    plugins: [
        extractCss,

        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(isProduction ? 'production' : 'development'),
        }),
    ],

    devtool: isProduction ? false : 'source-map',
};

// Production optimizations
if (isProduction) {
    config.plugins.push(
        new UglifyJsPlugin({
            sourceMap: false,
            uglifyOptions: {
                compress: {
                    warnings: false,
                    drop_console: true,
                },
                output: {
                    comments: false,
                }
            }
        })
    );

    config.plugins.push(
        new OptimizeCSSPlugin({
            cssProcessorOptions: {
                safe: true,
                // Disable rule-merging so the scope plugin's output isn't collapsed
                // back into unscoped selectors by cssnano.
                mergeRules: false,
                mergeLonghand: false,
            }
        })
    );
}

module.exports = config;
