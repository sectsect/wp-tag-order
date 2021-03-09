const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const ESLintPlugin = require('eslint-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
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
  // plugins.push(
  //   new SVGSpritemapPlugin(path.resolve(sourcePath, 'assets/images/svg/raw/**/*.svg'), {
  //     output: {
  //       filename: '../../../dist/assets/images/svg/symbol.svg',
  //       svgo: {
  //         plugins: [
  //           {
  //             addClassesToSVGElement: {
  //               classNames: ['svg-icon-lib'],
  //             }
  //           },
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
    plugins.push(
      new SizePlugin({
        writeFile: false,
      }),
    );
  }
  if (isDev(env)) {
    plugins.push(
      new ForkTsCheckerWebpackPlugin({
        eslint: {
          files: './src/assets/ts/**/*',
        }
      }),
    );
    plugins.push(
      new ForkTsCheckerNotifierWebpackPlugin({
        skipSuccessful: true,
        title: 'TypeScript',
      }),
    );
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
    entry: WebpackSweetEntry(path.resolve(sourcePath, 'assets/ts/**/*.ts*'), 'ts', 'ts'),
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
              loader: 'babel-loader',
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
    entry: WebpackSweetEntry(path.resolve(sourcePath, 'assets/scss/**/*.scss'), 'scss', 'scss'),
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
    devtool: isProd(env) ? false : 'inline-cheap-source-map',
    performance: {
      hints: isProd(env) ? 'warning' : false,
      maxEntrypointSize: 300000, // The default value is 250000 (bytes)
    },
  },
];
