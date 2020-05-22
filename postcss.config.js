const postcssClearfix = require('postcss-clearfix');
const pxtorem = require('postcss-pxtorem')({
  replace: false,
});
const postcssHexrgba = require('postcss-hexrgba');
const postcssFlexbugsFixes = require('postcss-flexbugs-fixes');
const postcssSortMediaQueries = require('postcss-sort-media-queries');
const autoprefixer = require('autoprefixer')({
  grid: 'autoplace',
});
const postcssSorting = require('postcss-sorting')({
  'properties-order': 'alphabetical',
});
const validator = require('postcss-validator');
const postcssReporter = require('postcss-reporter')({
  positionless: 'last',
});

module.exports = {
  plugins: [
    postcssClearfix,
    pxtorem,
    postcssHexrgba,
    postcssFlexbugsFixes,
    postcssSortMediaQueries,
    autoprefixer,
    postcssSorting,
    validator,
    postcssReporter,
  ],
};
