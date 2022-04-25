const webpack = require('webpack');
const { merge } = require('webpack-merge');
const path = require('path');

const webpackClient = require('./webpack.client.js');

const watchConf = merge(
  {
    devtool: 'eval-source-map',
    plugins: [
      new webpack.DefinePlugin({
        USE_HOT_ASSETS: true,
      }),
    ],
    watch: true,
    watchOptions: {
      poll: 1000,
      // we need to ignore this directory to prevent infinite build loop
      ignored: [path.resolve('public/fonts')],
    },
  },
  webpackClient,
);

module.exports = watchConf;
