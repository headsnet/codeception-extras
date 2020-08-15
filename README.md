# Codeception Extras

[![Build Status](https://travis-ci.com/headsnet/codeception-extras.svg?branch=master)](https://travis-ci.com/headsnet/codeception-extras)
[![Latest Stable Version](https://poser.pugx.org/headsnet/codeception-extras/v)](//packagist.org/packages/headsnet/codeception-extras)
[![Total Downloads](https://poser.pugx.org/headsnet/codeception-extras/downloads)](//packagist.org/packages/headsnet/codeception-extras)
[![License](https://poser.pugx.org/headsnet/codeception-extras/license)](//packagist.org/packages/headsnet/codeception-extras)

This package provides extensions for the Codeception test framework.

## Available Extensions

__WebDriver extensions:__
* [JS Logger](doc/js-console-logger.md) - log Javascript console messages
* [Symfony Profiler URL](doc/symfony-profiler-url.md) - get Profile link for failed tests
* [W3C HTML Validation](doc/html-validator.md) - validate HTML source code

__Other Extensions__
* [Wait After Test](doc/wait-after-test.md) - add a delay after each test

## Requirements

* PHP >=7.2
* Codeception

## Installation

Install the package via Composer

```shell script
composer require --dev headsnet/codeception-extras
```

For extensions that require it, configure the WebDriver module.

```yaml
WebDriver:
    url: 'http://myapp.com'
    browser: chrome
    host: chrome
    port: 4444
    window_size: false
    capabilities:
        webStorageEnabled: true
        javascriptEnabled: true
        'goog:loggingPrefs':
            performance: 'ALL'
        'goog:chromeOptions':
            perfLoggingPrefs:
                enableNetwork: true
```

### Contributing

Contributions are welcome. Please submit pull requests with one fix/feature per
pull request.

Composer scripts are configured for your convenience:

```
> composer test       # Run test suite
> composer cs         # Run coding standards checks
> composer cs-fix     # Fix coding standards violations
> composer static     # Run static analysis with Phpstan
```

