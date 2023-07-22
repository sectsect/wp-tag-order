const webpack = require('webpack');
const path = require('path');
const ESLintPlugin = require('eslint-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
// const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
const { WebpackSweetEntry } = require('@sect/webpack-sweet-entry');
const NotifierPlugin = require('@soda/friendly-errors-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const notifier = require('node-notifier');
const SizePlugin = require('size-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

const sourcePath = path.join(__dirname, 'src');
const buildPath = path.join(__dirname, '');

// For Detection Environment  @ https://webpack.js.org/api/cli/#environment-options
const isProd = env => env?.production;
const isDev = env => env?.development;

// http://jonnyreeves.co.uk/2016/simple-webpack-prod-and-dev-config/
const getJSPlugins = env => {
  const plugins = [];

  // plugins.push(
  //   new webpack.ProvidePlugin({
  //     $: 'jquery',
  //     jQuery: 'jquery',
  //     'window.jQuery': 'jquery',
  //     R: 'rambda',
  //   }),
  // );
  plugins.push(
    new ESLintPlugin({
      // files: ['./src/**/*.ts'],
      context: 'src/assets',
      extensions: ['ts', 'tsx', 'js', 'jsx'],
      fix: true,
      emitError: true,
      lintDirtyModulesOnly: true,
    }),
  );
  if (isProd(env)) {
    plugins.push(
      new SizePlugin({
        writeFile: false,
      }),
    );
  }
  if (isDev(env)) {
    plugins.push(new ForkTsCheckerWebpackPlugin());
    // plugins.push(
    //   new ForkTsCheckerNotifierWebpackPlugin({
    //     skipSuccessful: true,
    //     title: 'TypeScript',
    //   }),
    // );
    plugins.push(
      new BundleAnalyzerPlugin({
        // analyzerMode: 'static',
        // reportFilename: path.join(__dirname, 'report.html'),
        openAnalyzer: false,
      }),
    );
  }
  plugins.push(
    new NotifierPlugin({
      onErrors: (severity, errors) => {
        if (severity !== 'error') {
          return;
        }
        const error = errors[0];
        notifier.notify({
          title: 'Webpack error',
          message: `${severity}: ${error.name}`,
          sound: 'Bottle',
          subtitle: error.file || '',
        });
      },
    }),
  );

  return plugins;
};

const getCSSPlugins = env => {
  const plugins = [];

  plugins.push(new RemoveEmptyScriptsPlugin());
  plugins.push(
    new StyleLintPlugin({
      files: 'src/assets/css/**/*.css',
      lintDirtyModulesOnly: true,
      fix: true,
    }),
  );
  plugins.push(
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
  );
  if (isProd(env)) {
    plugins.push(
      new SizePlugin({
        writeFile: false,
      }),
    );
  }
  plugins.push(
    new NotifierPlugin({
      onErrors: (severity, errors) => {
        if (severity !== 'error') {
          return;
        }
        const error = errors[0];
        notifier.notify({
          title: 'Webpack error',
          message: `${severity}: ${error.name}`,
          sound: 'Bottle',
          subtitle: error.file || '',
        });
      },
    }),
  );

  return plugins;
};

module.exports = env => [
  {
    entry: WebpackSweetEntry(
      path.resolve(sourcePath, 'assets/ts/**/*.ts*'),
      'ts',
      'ts',
    ),
    output: {
      path: path.resolve(buildPath, 'assets/js'),
      filename: '[name].js',
    },
    // Persistent Caching @ https://github.com/webpack/changelog-v5/blob/master/guides/persistent-caching.md
    cache: {
      type: 'filesystem',
      buildDependencies: {
        config: [__filename],
      },
      name: isProd(env) ? `js-production` : `js-development`,
    },
    module: {
      rules: [
        {
          // test: /\.(ts|js)$/,
          test: /\.(t|j)sx?$/,
          exclude: /node_modules/,
          // test: /\.(mjs|js)$/,
          // exclude: /node_modules\/(?!(rambda|quicklink)\/).*/,
          use: [
            {
              loader: 'swc-loader',
              options: {
                cacheDirectory: true,
              },
            },
          ],
        },
        // Modernizr
        {
          test: /\.modernizrrc.js$/,
          use: ['@sect/modernizr-loader'],
        },
        {
          test: /\.modernizrrc(\.json)?$/,
          use: ['@sect/modernizr-loader', 'json-loader'],
        },
        // Modernizr
      ],
    },
    externals: {
      jquery: 'jQuery',
    },
    // Modernizr
    resolve: {
      extensions: ['.tsx', '.ts', '.jsx', '.js'],
      modules: ['node_modules'],
      alias: {
        modernizr$: path.resolve(__dirname, '.modernizrrc'),
      },
    },
    // Modernizr
    optimization: {
      splitChunks: {
        cacheGroups: {
          commons: {
            name: 'commons',
            chunks: 'initial',
            minChunks: 2,
          },
        },
      },
      minimizer: [
        new TerserPlugin({
          parallel: true,
          terserOptions: {
            compress: {
              drop_console: true,
            },
            output: {
              comments: false,
            },
          },
          extractComments: false,
        }),
      ],
    },
    plugins: getJSPlugins(env),
    devtool: isProd(env) ? false : 'inline-cheap-source-map',
    performance: {
      hints: isProd(env) ? 'warning' : false,
      maxEntrypointSize: 300000, // The default value is 250000 (bytes)
    },
  },
  {
    entry: WebpackSweetEntry(
      path.resolve(sourcePath, 'assets/css/**/*.css'),
      'css',
      'css',
    ),
    output: {
      path: path.resolve(buildPath, 'assets/css'),
      // filename: '[name].css',
    },
    // Persistent Caching @ https://github.com/webpack/changelog-v5/blob/master/guides/persistent-caching.md
    cache: {
      type: 'filesystem',
      buildDependencies: {
        config: [__filename],
      },
      name: isProd(env) ? `css-production` : `css-development`,
    },
    module: {
      rules: [
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false,
              },
            },
            { loader: 'postcss-loader' },
          ],
        },
      ],
    },
    externals: {},
    resolve: {
      modules: ['node_modules'],
    },
    optimization: {
      minimizer: [
        new CssMinimizerPlugin({
          minimizerOptions: {
            preset: [
              'default',
              {
                discardComments: {
                  removeAll: true,
                },
              },
            ],
          },
        }),
      ],
    },
    plugins: getCSSPlugins(env),
    devtool: isProd(env) ? false : 'inline-cheap-source-map',
    performance: {
      hints: isProd(env) ? 'warning' : false,
      maxEntrypointSize: 300000, // The default value is 250000 (bytes)
    },
  },
];
