declare module '@soda/friendly-errors-webpack-plugin' {
  type WebpackError = import('webpack').WebpackError;

  class FriendlyErrorsWebpackPlugin extends import('webpack').Plugin {
    constructor(options?: FriendlyErrorsWebpackPlugin.Options);
    apply(compiler: import('webpack').Compiler): void;
  }

  namespace FriendlyErrorsWebpackPlugin {
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
}
