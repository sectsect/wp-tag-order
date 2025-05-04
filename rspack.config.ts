/* eslint-disable import/no-extraneous-dependencies */
import path from 'path';

import type { Configuration } from '@rspack/cli';
import * as rspack from '@rspack/core';
import type { SwcLoaderOptions } from '@rspack/core';
import { WebpackSweetEntry } from '@sect/webpack-sweet-entry';
import NotifierPlugin from '@soda/friendly-errors-webpack-plugin';
import dotenv from 'dotenv';
import ESLintPlugin from 'eslint-rspack-plugin';
import ForkTsCheckerNotifierWebpackPlugin from 'fork-ts-checker-notifier-webpack-plugin';
import ForkTsCheckerWebpackPlugin from 'fork-ts-checker-webpack-plugin';
import notifier from 'node-notifier';
import StyleLintPlugin from 'stylelint-webpack-plugin';
// import SVGSpritemapPlugin from 'svg-spritemap-webpack-plugin';
import type { WebpackError } from 'webpack/types';
import { BundleAnalyzerPlugin } from 'webpack-bundle-analyzer';
import RemoveEmptyScriptsPlugin from 'webpack-remove-empty-scripts';

dotenv.config();

const sourcePath = path.resolve(__dirname, 'src');
const buildPath = path.resolve(__dirname, '');

// For dotenv
// console.log(process.env.AWS_ACCESS_KEY_ID);

const isProduction = process.env.NODE_ENV === 'production';

const getJSPlugins = () => {
  const plugins: rspack.WebpackPluginInstance[] = [];

  plugins.push(
    new rspack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      // R: 'rambda',
    }),
  );

  plugins.push(
    new ESLintPlugin({
      context: path.resolve(sourcePath, 'assets'),
      extensions: ['ts', 'tsx', 'js', 'jsx'],
      fix: true,
      emitError: true,
      lintDirtyModulesOnly: true,
      configType: 'flat',
      eslintPath: require.resolve('eslint/use-at-your-own-risk'),
    }),
  );
  // plugins.push(
  //   new SVGSpritemapPlugin(path.resolve(sourcePath, 'assets/images/svg/raw/**/*.svg'), {
  //     output: {
  //       filename: '../images/svg/symbol.svg',
  //       svg: {
  //         attributes: {
  //           class: 'svg-icon-lib',
  //         },
  //       },
  //       svgo: {
  //         plugins: [
  //           // {
  //           //   name: 'addClassesToSVGElement',
  //           //   params: {
  //           //     classNames: ['svg-icon-lib'],
  //           //   },
  //           // },
  //           {
  //             name: 'removeTitle',
  //             active: false,
  //           },
  //           // {
  //           //   name: 'removeAttrs',
  //           //   params: {
  //           //     attrs: 'fill',
  //           //   },
  //           // },
  //           {
  //             name: 'convertStyleToAttrs',
  //             active: true,
  //           },
  //           // {
  //           //   name: 'removeStyleElement',
  //           // },
  //           {
  //             name: 'inlineStyles',
  //           },
  //           {
  //             name: 'cleanupEnableBackground',
  //           },
  //         ],
  //       },
  //     },
  //     sprite: {
  //       prefix: 'icon-',
  //     },
  //   }),
  // );
  if (!isProduction) {
    plugins.push(new ForkTsCheckerWebpackPlugin());
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
      onErrors: (severity: string, errors: WebpackError[]) => {
        if (severity !== 'error') return;
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

const getCSSPlugins = () => {
  const plugins: rspack.WebpackPluginInstance[] = [];

  plugins.push(new RemoveEmptyScriptsPlugin());

  plugins.push(
    new StyleLintPlugin({
      files: path.resolve(sourcePath, 'assets/css/**/*.css'),
      lintDirtyModulesOnly: true,
      fix: true,
    }),
  );

  plugins.push(
    new rspack.CssExtractRspackPlugin({
      filename: '[name].css',
    }),
  );

  plugins.push(
    new NotifierPlugin({
      onErrors: (severity: string, errors: WebpackError[]) => {
        if (severity !== 'error') return;
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

const config = () =>
  [
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
      module: {
        rules: [
          {
            test: /\.(j|t)s$/,
            exclude: [/[\\/]node_modules[\\/]/],
            // exclude: /node_modules\/(?!(rambda|quicklink)\/).*/,
            loader: 'builtin:swc-loader',
            options: {
              jsc: {
                parser: {
                  syntax: 'typescript',
                },
              },
            } satisfies SwcLoaderOptions,
            type: 'javascript/auto',
          },
        ],
      },
      externals: {
        jquery: 'jQuery',
      },
      resolve: {
        extensions: ['.tsx', '.ts', '.jsx', '.js'],
        modules: [path.resolve(__dirname, 'node_modules')],
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
        minimizer: [new rspack.SwcJsMinimizerRspackPlugin()],
      },
      plugins: getJSPlugins(),
      devtool: isProduction ? false : 'inline-cheap-source-map',
      performance: {
        hints: isProduction ? 'warning' : false,
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
      module: {
        rules: [
          {
            test: /\.css$/,
            use: [
              rspack.CssExtractRspackPlugin.loader,
              {
                loader: 'css-loader',
                options: {
                  url: false,
                },
              },
              'postcss-loader',
            ],
            type: 'javascript/auto',
          },
        ],
      },
      resolve: {
        modules: [path.resolve(__dirname, 'node_modules')],
      },
      optimization: {
        minimizer: [new rspack.LightningCssMinimizerRspackPlugin()],
      },
      plugins: getCSSPlugins(),
      devtool: isProduction ? false : 'inline-cheap-source-map',
      performance: {
        hints: isProduction ? 'warning' : false,
        maxEntrypointSize: 300000, // The default value is 250000 (bytes)
      },
    },
  ] as const satisfies Configuration[];

// eslint-disable-next-line import/no-default-export
export default config;
