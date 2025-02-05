const path = require('path');

function getRulesConf() {
  return {
    module: {
      // We need to expose our jQuery with the $ and jQuery variable for sonata qnd others dependencies
      rules: [
        {
          test: [
            // These dependencies have es6 syntax which ie11 doesn't like. See https://github.com/formatjs/react-intl/blob/master/docs/Upgrade-Guide.md#esm-build
            path.resolve('node_modules/react-intl'),
            path.resolve('node_modules/intl-messageformat'),
            path.resolve('node_modules/intl-messageformat-parser'),
            /\.(js|jsx)$/i,
          ],
          exclude: [/(-test\.js|\.snap|-stories\.js)$/],
          use: [
            {
              loader: 'babel-loader',
              options: {
                // This is a feature of `babel-loader` for webpack (not Babel itself).
                // It enables caching results in ./node_modules/.cache/babel-loader/
                // directory for faster rebuilds.
                cacheDirectory: true,
                // Don't waste time on Gzipping the cache
                cacheCompression: false,
              },
            },
          ],
        },
      ],
    },
  };
}

module.exports = getRulesConf;
