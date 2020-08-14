
# W3C HTML Validation

This extension will take the source code retrieved by the WebDriver module
during each test, and pass it to a W3C validator instance
(https://validator.w3.org/).

Results can be shown in the test output, and also written to a log file for each
test.

## Usage

```yaml
extensions:
  enabled:
    - Headsnet\CodeceptionExtras\Extensions\HtmlValidator\HtmlValidator:
        validator_url: 'http://validator.docker'
        output_format: 'gnu'     # Optional - html|gnu - Defaults to 'gnu'

```

## Validator Configuration

#### Local Validator

To improve test speed, and to avoid over-using the live validator service, it is
recommended to use a local installation of the validator.

This can easily be done for example with a docker container as per the validator
documentation at https://hub.docker.com/r/validator/validator/

```shell script
docker run -it --rm -p 8888:8888 validator/validator:latest
```

Or via Docker Compose:

```yaml
version: '2'
services:
  vnu:
    image: validator/validator
    ports:
      - '8888:8888'
    network_mode: host
```

#### Remote Validator

Alternatively, you can use the remote validator service directly - but you may
violate the terms of service as the validator is hit for every test run! This
will also be much slower as the HTML source code and the corresponding results
have to be sent over the wire.

```yaml
validator_url: 'https://validator.w3.org/'
```

&laquo; [Back to package homepage](../../../)
