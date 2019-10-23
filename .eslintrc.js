module.exports = {
  "env": {
    "es6": true,
    "browser": true,
    "node": true
  },
  "parser": "@typescript-eslint/parser",
  "parserOptions": {
    // "ecmaVersion": 2020,
    "sourceType": "module",
    "project": "./tsconfig.json"
  },
  "extends": [
    "airbnb-typescript",
    "plugin:@typescript-eslint/recommended",
    "plugin:prettier/recommended",
    "prettier/@typescript-eslint"
  ],
  "plugins": [
    "@typescript-eslint"
  ],
  "rules": {
    "indent": [2, 2],
    "func-names": 0,
    "import/extensions": 0,
    // "import/extensions": ["error", "always", {
    //     "js": "never",
    //     "jsx": "never",
    //     "ts": "never",
    //     "tsx": "never"
    // }],
    "import/no-extraneous-dependencies": ["error", {
      "devDependencies": true,
      "optionalDependencies": false
    }],
    "import/prefer-default-export": "off",
    "import/no-default-export": "error",
    "max-len": [2, {
      "code": 200,
      "tabWidth": 2,
      "ignoreUrls": true,
      "ignoreComments": true,
      "ignoreTrailingComments": true,
      "ignoreStrings": true
    }],
    "no-alert": 0,
    "no-console": 0,
    "no-new": 0,
    "no-shadow": 0,
    "no-tabs": 0,
    "no-undef": 0,
    "no-underscore-dangle": 0,
    "no-unused-vars": 0,
    "no-use-before-define": 0,
    "@typescript-eslint/no-explicit-any": 0,
    "prettier/prettier": "error"
  },
  "settings": {
    "import/extensions": [".js", ".jsx", ".json", ".ts", ".tsx"],
    "import/resolver": {
      "webpack": {
        "config": "webpack.config.js"
      }
    }
  }
};
