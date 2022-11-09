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
    'airbnb',
    'airbnb-typescript',
    'plugin:@typescript-eslint/recommended',
    'plugin:prettier/recommended',
  ],
  plugins: [
    // "@babel",
    'prefer-arrow',
    '@typescript-eslint',
    'unused-imports',
    'eslint-plugin-tsdoc',
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
    'prefer-arrow/prefer-arrow-functions': [
      'warn',
      {
        disallowPrototype: true,
        singleReturnOnly: false,
        classPropertiesAllowed: false,
      },
    ],
    'no-unused-vars': 'off',
    '@typescript-eslint/no-unused-vars': 'off',
    'unused-imports/no-unused-imports': 'error',
    'unused-imports/no-unused-vars': [
      'error',
      {
        vars: 'all',
        // varsIgnorePattern: '^_',
        varsIgnorePattern: '^Window$',
        args: 'after-used',
        argsIgnorePattern: '^_',
      },
    ],
    'no-alert': 0,
    'no-console': 0,
    'tsdoc/syntax': 'warn',
    '@typescript-eslint/explicit-function-return-type': 0,
    '@typescript-eslint/interface-name-prefix': 0,
    '@typescript-eslint/naming-convention': [
      'error',
      {
        selector: ['variable', 'function', 'parameter'],
        format: ['camelCase'],
        leadingUnderscore: 'allow',
      },
      {
        selector: 'variable',
        types: ['boolean'],
        format: ['PascalCase'],
        prefix: ['is', 'should'],
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
        selector: 'typeParameter',
        format: ['PascalCase'],
        prefix: ['T'],
      },
      {
        selector: 'enum',
        format: ['PascalCase'],
      },
    ],
    '@typescript-eslint/no-explicit-any': 0,
    '@typescript-eslint/prefer-nullish-coalescing': 'error',
    '@typescript-eslint/prefer-optional-chain': 'error',
    'prettier/prettier': 'error',
  },
  settings: {
    react: {
      version: '18.2.0',
    },
    'import/extensions': ['.js', '.jsx', '.json', '.ts', '.tsx'],
    'import/resolver': {
      webpack: {
        config: 'webpack.config.js',
      },
    },
  },
};
