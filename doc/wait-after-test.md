
# Wait After Test

Automatically apply a delay after each test.

#### Use Case

You have a page which performs various AJAX requests on page load.

When running an acceptance test on this page, Codeception removes/replaces the
database before all the AJAX calls have completed. The test then fails.

Introducing a delay after each test gives the AJAX calls time to complete
before the database is replaced for the next test.

## Usage

```yaml
extensions:
  enabled:
    - Headsnet\CodeceptionExtras\Extensions\WaitAfterTest\WaitAfterTest:
          wait_time: 1     # Optional - Delay in seconds
```

&laquo; [Back to package homepage](../../../)
