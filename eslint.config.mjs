/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable no-underscore-dangle */
/* eslint-disable import/no-anonymous-default-export */

// Node.js path and URL utilities
import path from 'node:path';
import { fileURLToPath } from 'node:url';

// ESLint core and compatibility utilities
import js from '@eslint/js';
import { FlatCompat } from '@eslint/eslintrc';
import { fixupPluginRules } from '@eslint/compat';
import globals from 'globals';

// ESLint plugins for various technologies and best practices
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import jsxA11Y from 'eslint-plugin-jsx-a11y';
import typescriptEslint from '@typescript-eslint/eslint-plugin';
// import tseslint from 'typescript-eslint';
import unusedImports from 'eslint-plugin-unused-imports';
import tailwindcss from 'eslint-plugin-tailwindcss';
import tsdoc from 'eslint-plugin-tsdoc';
import _import from 'eslint-plugin-import';
import prettier from 'eslint-plugin-prettier';
import eslintPluginPrettierRecommended from 'eslint-plugin-prettier/recommended';
import testingLibrary from 'eslint-plugin-testing-library';
import vitest from 'eslint-plugin-vitest';
import deprecationPlugin from 'eslint-plugin-deprecation';
// import nextPlugin from '@next/eslint-plugin-next';
import pluginQuery from '@tanstack/eslint-plugin-query';

// Resolve current file and directory paths
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
  baseDirectory: __dirname,
  recommendedConfig: js.configs.recommended,
  allConfig: js.configs.all,
});

export default [
  // Ignore specific files and directories
  {
    ignores: [
      '**/dist/**',
      '**/out/**',
      '**/build/**',
      '**/node_modules/**',
      '**/playwright-report/**',
      '**/coverage/**',
      '**/.next/**',
      '**/.storybook/**',
      '**/*.config.{js,cjs,mjs}',
      '**/vendor/**',
    ],
  },

  // Base configuration extensions
  ...compat.extends('airbnb', 'airbnb-typescript'),

  // Detailed settings for TypeScript and React files
  {
    files: ['**/*.{ts,tsx}', '**/*.{test,spec}.{ts,tsx}'],

    plugins: {
      react,
      'react-hooks': reactHooks,
      'jsx-a11y': jsxA11Y,
      '@typescript-eslint': typescriptEslint,
      // '@typescript-eslint': tseslint.plugin, // Commented out. This is because the @typescript-eslint plugin is already loaded by ...compat.extends('airbnb-typescript').
      // '@next/next': nextPlugin,
      'unused-imports': unusedImports,
      '@tanstack/query': pluginQuery,
      deprecation: fixupPluginRules(deprecationPlugin),
      tailwindcss,
      tsdoc,
      import: _import,
      prettier,
      'testing-library': testingLibrary,
      vitest,
    },

    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      // parser: tseslint.parser,
      parserOptions: {
        project: './tsconfig.json',
      },
      globals: {
        ...globals.browser,
        ...globals.node,
        ...globals.jquery,
        window: true,
        lazySizes: true,
      },
    },

    settings: {
      react: {
        version: '19.0.0',
      },
    },

    rules: {
      // Prettier rules
      'prettier/prettier': 'error',

      // React rules
      'react/require-default-props': 'off',
      'react/jsx-props-no-spreading': 'off',
      'react/no-danger': 'off',
      'react/function-component-definition': [
        2,
        {
          namedComponents: 'arrow-function',
          unnamedComponents: 'arrow-function',
        },
      ],

      // Accessibility rules
      'jsx-a11y/anchor-is-valid': 'off',
      'jsx-a11y/label-has-associated-control': [
        2,
        {
          assert: 'either',
        },
      ],
      'jsx-a11y/control-has-associated-label': 'off',

      // Next.js rules
      // '@next/next/no-img-element': 'off',

      // Import rules
      'import/order': [
        'error',
        {
          groups: ['builtin', 'external', 'internal'],
          pathGroups: [
            {
              pattern: 'react',
              group: 'external',
              position: 'before',
            },
          ],
          pathGroupsExcludedImportTypes: ['react'],
          'newlines-between': 'always',
          alphabetize: {
            order: 'asc',
            caseInsensitive: true,
          },
        },
      ],
      'import/prefer-default-export': 'off',

      // TypeScript rules
      '@typescript-eslint/comma-dangle': 'off',
      '@typescript-eslint/consistent-type-imports': 'error',
      '@typescript-eslint/no-unnecessary-condition': 'error',
      '@typescript-eslint/no-unused-vars': 'off',

      // Unused imports/variables rules
      'unused-imports/no-unused-imports': 'error',
      'unused-imports/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],

      // Other miscellaneous rules
      'no-underscore-dangle': 'off',
      'tailwindcss/no-custom-classname': 'off',
      'tsdoc/syntax': 'warn',
    },
  },

  // Additional rules for test files
  {
    files: ['**/*.{test,spec}.{ts,tsx}'],
    rules: {
      'vitest/consistent-test-it': ['error', { fn: 'test' }],
      'vitest/require-top-level-describe': [
        'error',
        { maxNumberOfTopLevelDescribes: 2 },
      ],
    },
  },

  // Keep Prettier configuration last to avoid conflicts
  eslintPluginPrettierRecommended,
];
