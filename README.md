# Funcational tests scaffolding

[![Build Status](https://travis-ci.org/keboola/functional-tests-scaffolding.svg?branch=master)](https://travis-ci.org/keboola/functional-tests-scaffolding)

# Usage

In the tests folder create a directory structure mimicking the directory structure in production:
```
/path/to/tests
└─test-name
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

Then run the script with path to the tests directory.

`vendor/bin/functional /path/to/tests`

Result:

```
Test "test-name"
✓ Suceeded
```

The script then executes `/code/src/run.php` with `KBC_DATADIR` set to the test directory. There can be any number of test directories and the script automatically discovers them. 

If there is no `expected` directory in the test's directory, the script expects `run.php` to fail:
```
Test "failing-test-name"
✓ Execution failed as expected (1)
Invalid configuration for path "root.parameters.source_encoding": Source encoding is not valid
```

## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/functional-tests-scaffolding
cd functional-tests-scaffolding
docker-compose run --rm dev /bin/bash
$ composer install
$ composer ci
```
