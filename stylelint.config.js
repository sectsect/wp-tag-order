// @ https://stylelint.io/user-guide/example-config
module.exports = {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-prettier',
    'stylelint-config-recess-order',
  ],
  plugins: ['stylelint-prettier', 'stylelint-scss'],
  rules: {
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['mixin', 'include', 'extend', 'function', 'return', 'if', 'else', 'each'],
      },
    ],
    // "declaration-block-no-duplicate-properties": [
    //   true,
    //   {
    //     ignore: ["consecutive-duplicates-with-different-values"]
    //   }
    // ],
    'block-no-empty': null,
    'no-descending-specificity': null,
    'no-duplicate-selectors': null,
    'prettier/prettier': true,
    'scss/at-rule-no-unknown': true
  },
};
