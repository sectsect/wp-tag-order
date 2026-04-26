import { RsdoctorRspackPlugin } from '@rsdoctor/rspack-plugin';
import * as rspack from '@rspack/core';
import { WebpackSweetEntry } from '@sect/webpack-sweet-entry';
import dotenv from 'dotenv';
import ForkTsCheckerNotifierWebpackPlugin from 'fork-ts-checker-notifier-webpack-plugin';
import ForkTsCheckerWebpackPlugin from 'fork-ts-checker-webpack-plugin';
import StyleLintPlugin from 'stylelint-webpack-plugin';
import RemoveEmptyScriptsPlugin from 'webpack-remove-empty-scripts';

import type { Configuration } from '@rspack/cli';
import type { SwcLoaderOptions } from '@rspack/core';
// import SVGSpritemapPlugin from 'svg-spritemap-webpack-plugin';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

// biome-ignore lint/style/useNamingConvention: __filename/__dirname mirror Node.js CJS globals
const __filename = fileURLToPath(import.meta.url);
// biome-ignore lint/style/useNamingConvention: __filename/__dirname mirror Node.js CJS globals
const __dirname = path.dirname(__filename);

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
      // biome-ignore lint/style/useNamingConvention: jQuery is a global variable
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
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
  }

  if (process.env.RSDOCTOR) {
    plugins.push(new RsdoctorRspackPlugin({}));
  }

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

export default config;
