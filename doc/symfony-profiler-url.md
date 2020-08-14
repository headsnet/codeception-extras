
# Symfony Profiler URL

Get the URL of the Symfony Profiler for any requests that error.
The URL is displayed in the test output.

## Requirements

This extension requires the Symfony test environment to have the Profiler
enabled:

```yaml
# config/packages/test/framework.yaml
framework:
    profiler:
        collect: true
```

Additionally, you may want to store profiler results from the test environment
in the same directory as the dev environment, so you can then use the Web
Profiler to view the results:

```yaml
# config/packages/test/framework.yaml
framework:
    profiler:
        dsn: 'file:%kernel.project_dir%/var/cache/dev/profiler'
```

## Usage

```yaml
extensions:
  enabled:
    - Headsnet\CodeceptionExtras\Extensions\SymfonyProfilerUrl\SymfonyProfilerUrl:
          profiler_link_base: 'https://my-app-domain.com/_profiler/'
```

&laquo; [Back to package homepage](../../../)
