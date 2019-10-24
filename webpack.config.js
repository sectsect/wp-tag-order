const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
// const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');
const { WebpackSweetEntry } = require('@sect/webpack-sweet-entry');
const SizePlugin = require('size-plugin');
const NotifierPlugin = require('friendly-errors-webpack-plugin');
const notifier = require('node-notifier');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

const sourcePath = path.join(__dirname, 'src');
const buildPath = path.join(__dirname, '');

// For Detection Environment  @ https://webpack.js.org/api/cli/#environment-options
const isProd = env => env && env.production;
const isDev = env => env && env.development;

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
  // plugins.push(
  //   new SVGSpritemapPlugin(path.resolve(sourcePath, 'assets/images/svg/raw/**/*.svg'), {
  //     output: {
  //       filename: '../../../dist/assets/images/svg/symbol.svg',
  //       svgo: {
  //         plugins: [
  //           { removeTitle: false },
  //           { removeAttrs: { attrs: 'fill' } },
  //           { removeStyleElement: true },
  //         ],
  //       },
  //     },
  //     sprite: {
  //       prefix: 'icon-',
  //     },
  //   }),
  // );
  if (isProd(env)) {
    plugins.push(new SizePlugin());
  }
  if (isDev(env)) {
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

  plugins.push(
    new FixStyleOnlyEntriesPlugin({
      silent: true,
    }),
  );
  plugins.push(
    new StyleLintPlugin({
      files: 'src/assets/scss/**/*.scss',
      syntax: 'scss',
      lintDirtyModulesOnly: true,
      fix: true,
    }),
  );
  plugins.push(
    new MiniCssExtractPlugin({
      filename: '[name].css',
      allChunks: true,
    }),
  );
  if (isProd(env)) {
    plugins.push(
      new OptimizeCssAssetsPlugin({
        cssProcessorPluginOptions: {
          preset: ['default', { discardComments: { removeAll: true } }],
        },
      }),
    );
    plugins.push(new SizePlugin());
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
    entry: WebpackSweetEntry(path.resolve(sourcePath, 'assets/ts/**/*.ts*'), 'ts', 'ts'),
    output: {
      path: path.resolve(buildPath, 'assets/js'),
      filename: '[name].js',
    },
    module: {
      rules: [
        {
          test: /\.(t|j)sx?$/,
          exclude: /node_modules/,
          // exclude: /node_modules\/(?!(rambda|quicklink)\/).*/,
          use: [
            { loader: 'babel-loader' },
            {
              loader: 'eslint-loader',
              options: {
                fix: true,
                failOnError: true,
                cache: false,
              },
            },
          ],
        },
      ],
    },
    externals: {
      jquery: 'jQuery',
    },
    resolve: {
      extensions: ['.tsx', '.ts', '.jsx', '.js'],
      modules: ['node_modules'],
    },
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
          cache: true,
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
    devtool: isProd(env) ? false : '#inline-source-map',
    performance: {
      hints: isProd(env) ? 'warning' : false,
      maxEntrypointSize: 300000, // The default value is 250000 (bytes)
    },
  },
  {
    entry: WebpackSweetEntry(path.resolve(sourcePath, 'assets/scss/**/*.scss'), 'scss', 'scss'),
    output: {
      path: path.resolve(buildPath, 'assets/css'),
      // filename: '[name].css',
    },
    module: {
      rules: [
        {
          test: /\.(sass|scss)$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false,
              },
            },
            { loader: 'postcss-loader' },
            { loader: 'sass-loader' },
          ],
        },
      ],
    },
    externals: {},
    resolve: {
      modules: ['node_modules'],
    },
    plugins: getCSSPlugins(env),
    devtool: isProd(env) ? false : '#inline-source-map',
    performance: {
      hints: isProd(env) ? 'warning' : false,
      maxEntrypointSize: 300000, // The default value is 250000 (bytes)
    },
  },
];
