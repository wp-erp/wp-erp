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

    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm.js',
            assets: path.resolve('./modules/accounting/assets/'),
            '@': path.resolve('./assets/src/'),
            frontend: path.resolve('./assets/src/frontend/'),
            // 'admin': path.resolve('./assets/src/admin/'),
            admin: path.resolve('./modules/accounting/assets/src/admin/')
        },
        modules: [
            path.resolve('./node_modules'),
            path.resolve(path.join(__dirname, 'assets/src/'))
        ]
    },

    plugins,

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                query: {
                    presets: ['@wordpress/babel-preset-default']
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
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
                use: [{
                    loader: 'url-loader',
                    options: {
                        name: '[name].[ext]',
                        outputPath: path.resolve(path.join(__dirname, 'assets/font/'))
                    }
                }]
            }
        ]
    }
};

var i18nJSConfig = {
    entry: './assets/vendor/i18n/i18n.js',
    output: {
        path: path.resolve(__dirname, './assets/js'),
        filename: 'i18n.js'
    }
};

module.exports = [mainConfig, i18nJSConfig];
