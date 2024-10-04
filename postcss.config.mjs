import postcssImport from 'postcss-import';
import postcssGlobalDataPlugin from '@csstools/postcss-global-data';
import postcssPresetEnvPlugin from 'postcss-preset-env';
import postcssSortMediaQueries from 'postcss-sort-media-queries';
import postcssCombineSelectors from 'postcss-combine-duplicated-selectors';
import postcssPixelToRemPlugin from 'postcss-pxtorem';
import postcssCalc from 'postcss-calc';
import postcssHexrgba from 'postcss-hexrgba';
import postcssReporterPlugin from 'postcss-reporter';

const postcssGlobalData = postcssGlobalDataPlugin({
  files: ['src/assets/css/_base/settings.css'],
});

const postcssPresetEnv = postcssPresetEnvPlugin({
  stage: 1,
  autoprefixer: {
    grid: 'autoplace',
  },
  features: {
    'nesting-rules': true,
    'custom-properties': {
      disableDeprecationNotice: true,
    },
    'has-pseudo-class': true,
  },
});

const pxtorem = postcssPixelToRemPlugin({
  replace: false,
});

const postcssReporter = postcssReporterPlugin({
  positionless: 'last',
});

export default {
  plugins: [
    postcssImport,
    postcssGlobalData,
    postcssPresetEnv,
    postcssSortMediaQueries,
    postcssCombineSelectors,
    pxtorem,
    postcssCalc,
    postcssHexrgba,
    postcssReporter,
  ],
};
