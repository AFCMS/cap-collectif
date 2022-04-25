// @flow

const path = require('path');
const webpack = require('webpack');
const merge = require('webpack-merge');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

const webpackConfig = require('./config');

const webpackJsx = require('./rules/webpack.jsx.js')();
const webpackScss = require('./rules/webpack.scss.js')();
const webpackFile = require('./rules/webpack.file.js')();

const devConf = {
  target: 'web',
  output: {
    pathinfo: true,
    filename: 'js/[name].js',
    path: webpackConfig.outputDir,
  },
  resolve: {
    alias: {
      '~relay': path.resolve(__dirname, '../frontend/js/__generated__/~relay'),
      '~ui': path.resolve(__dirname, '../frontend/js/components/Ui'),
      '~': path.resolve(__dirname, '../frontend/js'),
      '~fonts': path.resolve(__dirname, '../public/fonts'),
      '~svg': path.resolve(__dirname, '../public/svg'),
      react: path.resolve('./node_modules/react'),
    },
  },
  entry: {
    vendor: [
      path.join(
        webpackConfig.nodeModulesDir,
        'bootstrap-sass/assets/javascripts/bootstrap/alert.js',
      ),
      path.join(
        webpackConfig.nodeModulesDir,
        'bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
      ),
      path.join(webpackConfig.frontendDir, 'js/jsapi.js'),
      path.join(webpackConfig.frontendDir, 'js/googleCharts.js'),
      path.join(webpackConfig.frontendDir, 'js/browserUpdate.js'),
      path.join(webpackConfig.frontendDir, 'js/modernizr.js'),
    ],

    // Main file for JS/CSS
    app: [
      path.join(webpackConfig.frontendDir, 'js/app.js'),
      path.join(webpackConfig.frontendDir, 'js/registration.js'),
      path.join(webpackConfig.nodeModulesDir, 'leaflet/dist/leaflet.css'),
      path.join(webpackConfig.nodeModulesDir, 'quill/dist/quill.core.css'),
      path.join(webpackConfig.nodeModulesDir, 'quill/dist/quill.snow.css'),
      path.join(webpackConfig.nodeModulesDir, 'slick-carousel/slick/slick.css'),
      path.join(webpackConfig.nodeModulesDir, 'slick-carousel/slick/slick-theme.css'),
      path.join(webpackConfig.assetsDir, 'js/fancybox/jquery.fancybox.css'),
      path.join(webpackConfig.nodeModulesDir, 'react-datetime/css/react-datetime.css'),
      // Let style.scss at the bottom, it overrides some rules
      path.join(webpackConfig.srcDir, 'Resources/scss/style.scss'),
    ],

    // TODO: include rules for print.css with a media query and merge it with app.css
    // We separate print.css because it is needed only in the printed pages
    print: [path.join(webpackConfig.srcDir, 'Resources/scss/print.scss')],

    // Same as print but for backoffice
    'style-admin': [
      path.join(webpackConfig.srcDir, 'Resources/scss/style-admin.scss'),
      path.join(webpackConfig.nodeModulesDir, 'quill/dist/quill.core.css'),
      path.join(webpackConfig.nodeModulesDir, 'quill/dist/quill.snow.css'),
      path.join(webpackConfig.nodeModulesDir, 'leaflet/dist/leaflet.css'),
      path.join(webpackConfig.nodeModulesDir, 'leaflet-geosearch/dist/geosearch.css'),
      path.join(webpackConfig.nodeModulesDir, 'react-datetime/css/react-datetime.css'),
      path.join(webpackConfig.nodeModulesDir, 'jquery-minicolors/jquery.minicolors.css'),
    ],
  },
  mode: 'development',
  plugins: [
    // This plugin extracts CSS into separate files located in /css folder
    new MiniCssExtractPlugin({
      filename: 'css/[name].css',
    }),
    // Add some progress infos
    new webpack.ProgressPlugin(),
    // Remove a warning with es6-polyfill
    new webpack.IgnorePlugin(/vertx/),
    // Moment.js is an extremely popular library that bundles large locale files
    // by default due to how Webpack interprets its code.
    new MomentLocalesPlugin({
      // Locale names follow Moment.js behavior
      // – if a specific locale name (e.g. ru-ru) is absent, but a more generic locale (ru) is available,
      // the generic one will be kept bundled.
      localesToKeep: webpackConfig.locales,
      ignoreInvalidLocales: true,
    }),
    // Copy some legacy deps
    // TODO: Remove this legacy deps
    new CopyWebpackPlugin([
      {
        from: path.resolve(__dirname, '../node_modules/jquery-minicolors/jquery.minicolors.min.js'),
        to: path.resolve(__dirname, '../public/js/jquery.minicolors.js'),
      },
      {
        from: path.resolve(__dirname, '../node_modules/jquery-minicolors/jquery.minicolors.png'),
        to: path.resolve(__dirname, '../public/css/jquery.minicolors.png'),
      },
      {
        from: path.resolve(__dirname, '../assets/js/'),
        to: path.resolve(__dirname, '../public/js/'),
      },
    ]),
  ],
  module: {
    rules: [
      // Disable require.ensure as it's not a standard language feature.
      {
        parser: { requireEnsure: false },
      },
      // Process application JS with Babel.
      // The preset includes JSX, Flow, and some ESnext features.
    ],
  },
};

module.exports = merge.smart(devConf, webpackJsx, webpackFile, webpackScss);
