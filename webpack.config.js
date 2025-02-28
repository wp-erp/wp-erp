const webpack = require('webpack');
const path = require('path');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const OptimizeCSSPlugin = require('optimize-css-assets-webpack-plugin');

// Naming and path settings
var appName = 'wp-erp';

var exportPath = path.resolve(__dirname, './assets/js');

var entryPoints = {};

var rootEntryPoints = {
    // 'vue-pro-admin': './src/admin/main.js',
    // style: './less/style.less',
};

// @todo: Organize these settings asset compiling
entryPoints[`../../assets/js/erp-settings-bootstrap`] = `./includes/Settings/assets/src/bootstrap.js`;
entryPoints[`../../assets/js/erp-settings`] = `./includes/Settings/assets/src/main.js`;
entryPoints[`../../assets/css/erp-settings`] = `./includes/Settings/assets/less/settings.less`;

var moduleEntryPoints = {
    hr: {},

    crm: {},

    accounting: {
        frontend: 'assets/src/frontend/main.js',
        admin: 'assets/src/admin/main.js',
        bootstrap: 'assets/src/admin/bootstrap.js',
        style: 'assets/less/style.less'
        // 'master': 'assets/less/master.less'
    }
};

Object.keys(rootEntryPoints).forEach(function(output) {
    entryPoints[output] = rootEntryPoints[output];
});

Object.keys(moduleEntryPoints).forEach(function(erpModule) {
    var modulePath = `modules/${erpModule}`;

    Object.keys(moduleEntryPoints[erpModule]).forEach(function(moduleOutput) {
        entryPoints[`../../${modulePath}/assets/js/${moduleOutput}`] = `./${modulePath}/${moduleEntryPoints[erpModule][moduleOutput]}`;
    });
});

// Enviroment flag
var plugins = [];
var env = process.env.WEBPACK_ENV;

function isProduction() {
    return process.env.WEBPACK_ENV === 'production';
}

// extract css into its own file
const extractCss = new ExtractTextPlugin({
    filename(getPath) {
        return getPath('../css/[name].css').replace('assets/js', 'assets/css');
    }
});

plugins.push(extractCss);

// Extract all 3rd party modules into a separate 'vendor' chunk
plugins.push(new webpack.optimize.CommonsChunkPlugin({
    name: 'vendor',
    minChunks: ({ resource }) => /node_modules/.test(resource)
}));

// Compress extracted CSS. We are using this plugin so that possible
// duplicated CSS from different components can be deduped.
plugins.push(new OptimizeCSSPlugin({
    cssProcessorOptions: {
        safe: true,
        map: {
            inline: false
        }
    }
}));

// Differ settings based on production flag
if (isProduction()) {
    plugins.push(new UglifyJsPlugin({
        sourceMap: true
    }));

    plugins.push(new webpack.DefinePlugin({
        'process.env': {
            WEBPACK_ENV: JSON.stringify(env)
        }
    }));

    appName = '[name].js';
} else {
    appName = '[name].js';
}

plugins.push(new webpack.ProvidePlugin({
    $: 'jquery'
}));

var mainConfig = {
    entry: entryPoints,
    output: {
        path: exportPath,
        filename: appName,
        chunkFilename: 'chunks/[chunkhash].js',
        jsonpFunction: 'pluginWebpack'
    },
    devtool: isProduction() ? 'source-map' : 'eval-source-map',
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm.js',
            assets: path.resolve('./modules/accounting/assets/'),
            '@': path.resolve('./assets/src/'),
            frontend: path.resolve('./assets/src/frontend/'),
            // 'admin': path.resolve('./assets/src/admin/'),
            settings: path.resolve('./includes/Settings/assets/src/'),
            admin: path.resolve('./modules/accounting/assets/src/admin/')
        },
        extensions: ['.js', '.jsx', '.json'], // Add JSX support
        modules: [
            path.resolve('./node_modules'),
            path.resolve(path.join(__dirname, 'assets/src/'))
        ]
    },

    plugins,

    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                options: {

                    presets: [
                        '@babel/preset-env',
                        '@wordpress/babel-preset-default',
                        '@babel/preset-react'
                    ],
                    plugins: [
                        '@babel/plugin-transform-runtime',
                        '@babel/plugin-proposal-class-properties'
                    ]
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    extractCSS: true
                }
            },
            {
                test: /\.less$/,
                use: extractCss.extract({
                    use: [{
                        loader: 'css-loader'
                    }, {
                        loader: 'less-loader'
                    }]
                })
            },
            {
                test: /\.css$/,
                use: extractCss.extract({
                    fallback: 'style-loader',
                    use: ['css-loader']
                })
            },
            {
                test: /\.(woff(2)?|ttf|eot|svg|gif|png)(\?v=\d+\.\d+\.\d+)?$/,
                use: [{
                    loader: 'url-loader',
                    options: {
                        name: '[name].[ext]',
                        outputPath: path.resolve(path.join(__dirname, 'assets/font/'))
                    }
                }]
            }
        ]
    },
    performance: {
        hints: isProduction() ? 'warning' : false
    },
};

// React config for onboarding module
const reactExtractCss = new ExtractTextPlugin({
    filename: '../css/[name].css'
});

var reactConfig = {
    entry: {
        '../../modules/onboarding/assets/js/admin': './modules/onboarding/assets/src/main.js'
    },
    output: {
        path: exportPath,
        filename: '[name].js'
    },
    devtool: 'source-map',
    resolve: {
        extensions: ['.js', '.jsx', '.json', ".mjs"],
        modules: [
            path.resolve('./node_modules')
        ]
    },
    plugins: [
        reactExtractCss,
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(isProduction() ? 'production' : 'development')
            }
        })
    ],
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ["@babel/preset-env", {
                                "targets": {
                                    "browsers": ["last 2 versions", "safari >= 7"]
                                }
                            }],
                            "@babel/preset-react"
                        ],
                        plugins: [
                            "@babel/plugin-transform-runtime",
                            "@babel/plugin-proposal-class-properties",
                            "@babel/plugin-proposal-object-rest-spread"
                        ]
                    }
                }
            },
            {
                test: /\.css$/,
                use: reactExtractCss.extract({
                    fallback: 'style-loader',
                    use: ['css-loader']
                })
            },
            {
                test: /\.m?js$/, // Handle .js and .mjs files
                exclude: /node_modules\/(?!react-router)/,
                use: {
                  loader: "babel-loader",
                  options: {
                    presets: ["@babel/preset-env", "@babel/preset-react"],
                  },
                },
              },
        ]
    },
};

var i18nJSConfig = {
    entry: './assets/vendor/i18n/i18n.js',
    output: {
        path: path.resolve(__dirname, './assets/js'),
        filename: 'i18n.js'
    }
};

module.exports = [mainConfig, reactConfig, i18nJSConfig];
