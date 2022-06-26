const postcssImport = require('postcss-import');
const postcssPresetEnv = require('postcss-preset-env')({
  stage: 1, // Default: stage: 2   @ https://cssdb.org/#staging-process
  importFrom: 'src/assets/css/_base/settings.css',
  autoprefixer: {
    grid: 'autoplace',
  },
  features: {
    'nesting-rules': true,
    'custom-properties': {
      disableDeprecationNotice: true,
    },
  },
});
const postcssSortMediaQueries = require('postcss-sort-media-queries');
const postcssCombineSelectors = require('postcss-combine-duplicated-selectors');
const pxtorem = require('postcss-pxtorem')({
  replace: false,
});
const postcssCalc = require('postcss-calc');
const postcssPseudoIs = require('postcss-pseudo-is');
const postcssHexrgba = require('postcss-hexrgba');
const postcssFlexbugsFixes = require('postcss-flexbugs-fixes');
const postcssReporter = require('postcss-reporter')({
  positionless: 'last',
});

module.exports = {
  plugins: [
    postcssImport,
    postcssPresetEnv,
    postcssSortMediaQueries,
    postcssCombineSelectors,
    pxtorem,
    postcssCalc,
    postcssHexrgba,
    postcssPseudoIs,
    postcssFlexbugsFixes,
    postcssReporter,
  ],
};
