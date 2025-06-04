import type { Compiler, WebpackError } from 'webpack';
// eslint-disable-next-line import/no-extraneous-dependencies
import { Plugin } from 'webpack';

declare class FriendlyErrorsWebpackPlugin extends Plugin {
  constructor(options?: FriendlyErrorsWebpackPlugin.Options);
  apply(compiler: Compiler): void;
}

declare namespace FriendlyErrorsWebpackPlugin {
  type Severity = 'error' | 'warning';

  interface Options {
    compilationSuccessInfo?: {
      messages: string[];
      notes: string[];
    };
    onErrors?: (severity: Severity, errors: WebpackError[]) => void;
    clearConsole?: boolean;
    additionalFormatters?: Array<
      (errors: WebpackError[], type: Severity) => string[]
    >;
    additionalTransformers?: Array<(error: WebpackError) => WebpackError>;
  }
}

export = FriendlyErrorsWebpackPlugin;
