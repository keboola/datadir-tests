# Datadir tests

[![Build Status](https://travis-ci.org/keboola/datadir-tests.svg?branch=master)](https://travis-ci.org/keboola/datadir-tests)

# Usage

Require this package in you component

`composer require keboola/datadir-tests`

In the tests folder create a directory structure mimicking the directory structure in production:
```
/path/to/tests
└─test-name
  ├─expected-code
  ├─expected-stderr
  ├─expected
  │ └─data
  │   └─out
  │     ├─files
  │     └─tables
  ├─source
  │ └─data
  │   └─in
  │     ├─files
  │     └─tables
  └─config.json
```

Then create empty `/path/to/tests/DatadirTest` that extends `Keboola\DatadirTests\DatadirTestCase`:

```php
<?php

declare(strict_types=1);

namespace MyComponent\Tests;

use Keboola\DatadirTests\DatadirTestCase;

class DatadirTest extends DatadirTestCase
{
}

```

run it using

`vendor/bin/phpunit /path/to/tests/DatadirTest.php`


The script then executes `/code/src/run.php` with `KBC_DATADIR` set to the test directory. There can be any number of test directories and the script automatically discovers them using `DatadirTestsFromDirectoryProvider`. You can supply your own provider that implements `DatadirTestsProviderInterface`. It needs to return array of arrays (!) of `DatadirTestSpecificationInterface` instances.

When the `expected-code` file is present, the return code of execution is checked. The file contains a single number number - the execution code, allowed values are `0`, `1`, `2`.

When the `expected-stderr` file is present, the stderr output is checked. The file content is kept as is including special characters like \t,\s,\n,...

## What is `DatadirTestSpecificationInterface`?

`DatadirTestSpecificationInterface` contains all the information you need to create and assert a datadir test:
 * `getSourceDatadirDirectory(): ?string`: returns the directory that initializes the test. You should prepare `config.json` and potentially also `/in/files` or `/in/tables` contents. That directory is mirrored to the temporary directory that the test is ran in. `null` means just barebones directory structure is created and you need to create `config.json`, etc. in the temporary directory yourself.
 * `getExpectedReturnCode(): ?int`: expected exit code, `null` means "non-zero"
 * `getExpectedStdout(): ?string`: if supplied, whole stdout output is checked against supplied value
 * `getExpectedStderr(): ?string`: if supplied, whole stderr output is checked against supplied value
 * `getExpectedOutDirectory(): ?string`: if supplied, the temporary directory's **`out` directory** is compared with this directory after the component is ran. Any differences result in test failure.

 ## Custom test

 Just add a test method and reuse the existing helper methods in `AbstractDatadirTestCase`.

 ```php
public function testInvalidFile(): void
{
    // create specification manually
    $specification = new DatadirTestSpecification(
        __DIR__ . '/columns-auto/source/data',
        0,
        null,
        null,
        __DIR__ . '/columns-auto/expected/data/out'
    );

    // create temporary directory
    $tempDatadir = $this->getTempDatadir($specification);

    // modify temporary directory however you see fit
    file_put_contents($tempDatadir->getTmpFolder() . '/config.json', '{"parameters": []}');

    // run the script
    $process = $this->runScript($tempDatadir->getTmpFolder());

    // assert specification
    $this->assertMatchesSpecification($specification, $process, $tempDatadir->getTmpFolder());
}
 ```

## Development

Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/datadir-tests
cd datadir-tests
docker-compose run --rm dev /bin/bash
$ composer install
$ composer ci
```
