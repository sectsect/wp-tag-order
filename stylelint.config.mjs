/** @type {import('stylelint').Config} */
const config = {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-recess-order',
  ],
  plugins: ['stylelint-prettier'],
  rules: {
    'alpha-value-notation': 'number',
    'at-rule-empty-line-before': 'always',
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['mixin', 'define-mixin', 'if', 'else'],
      },
    ],
    'custom-media-pattern': '^([a-z][a-z0-9]*)(-[a-z0-9]+)*$',
    'custom-property-pattern': '^([a-z][a-z0-9]*)(-[a-z0-9]+)*$',
    'comment-empty-line-before': 'never',
    'declaration-block-no-duplicate-properties': [
      true,
      {
        ignore: ['consecutive-duplicates-with-different-values'],
      },
    ],
    // 'max-nesting-depth': 4,
    'no-descending-specificity': null,
    'prettier/prettier': true,
    'selector-class-pattern': null,
  },
};

export default config;
