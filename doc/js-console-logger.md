
# JS Logger

Logs information from the Javascript console of the browser, and stores it in a
text file in the Codeception `_output` directory for your perusal.

This extension requires the WebDriver module to be activated and configured as
per the installation instructions.

## Usage

```yaml
extensions:
  enabled:
    - Headsnet\CodeceptionExtras\Extensions\JsConsoleLogger\JsConsoleLogger:
      assert_no_warnings: true # default true
      assert_no_errors: true   # default true
      assert_no_console: true  # default true
```
### Options

- assert_no_warnings - assume that there are no warnings in javascript console.
- assert_no_errors - assume that there are no errors in javascript console.
- assert_no_console - assume that there are no console.log calls left in javascript.

&laquo; [Back to package homepage](../../../)
