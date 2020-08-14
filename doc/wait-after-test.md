
# Wait After Test

Apply a small delay after each test. This permits any late loading Javascript
that performs AJAX requests to the backend to still have a database file to
access.

Without this delay, the Codeception DB cleanup routine cleans up the database
and the HTTP request errors due to a missing or reset database.

## Usage

```yaml
extensions:
  enabled:
    - Headsnet\CodeceptionExtras\Extensions\WaitAfterTest\WaitAfterTest:
          wait_time: 1     # Optional - Delay in seconds
```

&laquo; [Back to package homepage](../../../)
