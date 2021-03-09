module.exports = {
  env: {
    es6: true,
    browser: true,
    node: true,
    jquery: true,
  },
  parser: '@typescript-eslint/parser',
  // "parser": "@babel/eslint-parser",
  parserOptions: {
    // "ecmaVersion": 2020,
    sourceType: 'module',
    project: './tsconfig.json',
  },
  extends: [
    'airbnb-typescript',
    'plugin:@typescript-eslint/recommended',
    'plugin:prettier/recommended',
  ],
  plugins: [
    // "@babel",
    '@typescript-eslint',
  ],
  globals: {
    window: true,
    lazySizes: true,
  },
  rules: {
    'import/extensions': 0,
    // "import/extensions": ["error", "always", {
    //     "js": "never",
    //     "jsx": "never",
    //     "ts": "never",
    //     "tsx": "never"
    // }],
    'import/no-extraneous-dependencies': [
      'error',
      {
        devDependencies: true,
        optionalDependencies: false,
      },
    ],
    'sort-imports': 0,
    'import/order': [
      'error',
      {
        groups: ['builtin', 'external', 'internal'],
        alphabetize: {
          order: 'asc',
        },
        'newlines-between': 'never',
      },
    ],
    'import/prefer-default-export': 'off',
    'import/no-default-export': 'error',
    'no-alert': 0,
    'no-console': 0,
    '@typescript-eslint/explicit-function-return-type': 0,
    '@typescript-eslint/interface-name-prefix': 0,
    '@typescript-eslint/naming-convention': [
      'error',
      {
        selector: 'parameter',
        format: ['camelCase'],
        leadingUnderscore: 'allow',
      },
      {
        selector: 'class',
        format: ['PascalCase'],
      },
      {
        selector: 'interface',
        format: ['PascalCase'],
        // custom: {
        //   regex: "^I[A-Z]",
        //   match: false
        // }
      },
      {
        selector: 'enum',
        format: ['PascalCase'],
      },
    ],
    '@typescript-eslint/no-explicit-any': 0,
    '@typescript-eslint/no-unused-vars': [
      'error',
      {
        varsIgnorePattern: '^Window$',
      },
    ],
    '@typescript-eslint/prefer-nullish-coalescing': 'error',
    '@typescript-eslint/prefer-optional-chain': 'error',
    'prettier/prettier': 'error',
  },
  settings: {
    'import/extensions': ['.js', '.jsx', '.json', '.ts', '.tsx'],
    'import/resolver': {
      webpack: {
        config: 'webpack.config.js',
      },
    },
  },
};
