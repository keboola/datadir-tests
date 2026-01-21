<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use InvalidArgumentException;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestsFromDirectoryProvider;
use Keboola\DatadirTests\DatadirTestSpecificationInterface;
use Keboola\DatadirTests\Exception\DatadirTestsException;
use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class DatadirTestCaseTest extends TestCase
{
    public function testExpectedSuccess(): void
    {
        $test = $this->getTestCase('001-successful');
        $test->setUp();
        $test->testDatadir($this->getSpecification('001-successful'));
        $this->addToAssertionCount(1);
    }

    public function testExpectedFail(): void
    {
        $test = $this->getTestCase('002-expected-fail');
        $test->setUp();
        $test->testDatadir($this->getSpecification('002-expected-fail'));
        $this->addToAssertionCount(1);
    }

    public function testUnexpectedFailure(): void
    {
        $test = $this->getTestCase('003-unexpected-failure');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('003-unexpected-failure'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting exit code', $e->getMessage());
            $comparisonFailure = $e->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $diff = $comparisonFailure->getDiff();
            $this->assertStringContainsString('-0', $diff, 'Expected code was not 0');
            $this->assertStringContainsString('+2', $diff, 'Actual code was not 2');
        }
    }

    public function testUnexpectedSuccess(): void
    {
        $test = $this->getTestCase('004-unexpected-success');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('004-unexpected-success'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting exit code', $e->getMessage());
            $comparisonFailure = $e->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $diff = $comparisonFailure->getDiff();
            $this->assertStringContainsString('-1', $diff, 'Expected should be 1');
            $this->assertStringContainsString('+0', $diff, 'Actual should be 0');
        }
    }

    public function testExpectedUserError(): void
    {
        $test = $this->getTestCase('005-expected-user-error');
        $test->setUp();
        $test->testDatadir($this->getSpecification('005-expected-user-error'));
        $this->addToAssertionCount(1);
    }

    public function testExpectedInternalError(): void
    {
        $test = $this->getTestCase('006-expected-internal-error');
        $test->setUp();
        $test->testDatadir($this->getSpecification('006-expected-internal-error'));
        $this->addToAssertionCount(1);
    }

    public function testExpectedUserErrorWithOutputFolder(): void
    {
        $test = $this->getTestCase('007-expected-user-error-with-output-folder');
        $test->setUp();
        $test->testDatadir($this->getSpecification('007-expected-user-error-with-output-folder'));
        $this->addToAssertionCount(1);
    }

    public function testUnexpectedInternalError(): void
    {
        $test = $this->getTestCase('008-unexpected-internal-error-instead-of-user-error');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('008-unexpected-internal-error-instead-of-user-error'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting exit code', $e->getMessage());
            $comparisonFailure = $e->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $diff = $comparisonFailure->getDiff();
            $this->assertStringContainsString(
                '-1',
                $diff,
                'Expected exit code should have been 1',
            );
            $this->assertStringContainsString(
                '+2',
                $diff,
                'Actual exit code should have been 2',
            );
        }
    }

    public function testUnexpectedUserError(): void
    {
        $test = $this->getTestCase('009-unexpected-user-error-instead-of-internal-error');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('009-unexpected-user-error-instead-of-internal-error'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting exit code', $e->getMessage());
            $comparisonFailure = $e->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $diff = $comparisonFailure->getDiff();
            $this->assertStringContainsString(
                '-2',
                $diff,
                'Expected exit code should have been 2',
            );
            $this->assertStringContainsString(
                '+1',
                $diff,
                'Actual exit code should have been 1',
            );
        }
    }

    public function testUnexpectedSuccessWithExplicitlyExpectedError(): void
    {
        $test = $this->getTestCase('011-unexpected-success-with-explicitly-expected-exit-code');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('011-unexpected-success-with-explicitly-expected-exit-code'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting exit code', $e->getMessage());
            $comparisonFailure = $e->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $diff = $comparisonFailure->getDiff();
            $this->assertStringContainsString(
                '-1',
                $diff,
                'Expected exit code should have been 1',
            );
            $this->assertStringContainsString(
                '+0',
                $diff,
                'Actual exit code should have been 0',
            );
        }
    }

    public function testInvalidExpectedExitCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('functional: Expecting invalid return code (7). Possible codes are: 0, 1, 2.');
        $this->getTestCase('010-invalid-expected-exit-code');
    }

    public function testFailsIfNeitherFolderNorCodeIsExpected(): void
    {
        $this->expectException(LogicException::class);
        $expectedMessage = 'functional: At least one of "expected/data/out" folder or "expected-code" file must exist';
        $this->expectExceptionMessage($expectedMessage);
        $this->getTestCase('012-neither-code-or-folder');
    }

    public function testExpectedStdoutMatch(): void
    {
        $test = $this->getTestCase('013-expected-stdout-match');
        $test->setUp();
        $test->testDatadir($this->getSpecification('013-expected-stdout-match'));
        $this->addToAssertionCount(1);
    }

    public function testExpectedStdoutNotMatch(): void
    {
        $test = $this->getTestCase('014-expected-stdout-not-match');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('014-expected-stdout-not-match'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $error = $e->getMessage();
            $this->assertStringContainsString('Failed asserting stdout output', $error);
            $this->assertStringContainsString('Failed asserting that string matches format description', $error);
        }
    }

    public function testExpectedStderrMatch(): void
    {
        $test = $this->getTestCase('015-expected-stderr-match');
        $test->setUp();
        $test->testDatadir($this->getSpecification('015-expected-stderr-match'));
        $this->addToAssertionCount(1);
    }

    public function testExpectedStderrNotMatch(): void
    {
        $test = $this->getTestCase('016-expected-stderr-not-match');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('016-expected-stderr-not-match'));
            $this->fail('Expected ExpectationFailedException was not thrown');
        } catch (ExpectationFailedException $e) {
            $error = $e->getMessage();
            $this->assertStringContainsString('Failed asserting stderr output', $error);
            $this->assertStringContainsString('Failed asserting that string matches format description', $error);
        }
    }

    public function testModifyConfig(): void
    {
        putenv('MY_TEST_VAR_123=some simple message');
        $test = $this->getTestCase('017-modify-config');
        $test->setUp();
        $test->testDatadir($this->getSpecification('017-modify-config'));
        $this->addToAssertionCount(1);
    }

    public function testInvalidConfig(): void
    {
        $test = $this->getTestCase('018-invalid-config');
        $test->setUp();

        try {
            $test->testDatadir($this->getSpecification('018-invalid-config'));
            $this->fail('Expected DatadirTestsException was not thrown');
        } catch (DatadirTestsException $e) {
            $this->assertStringContainsString('Cannot decode "config.json"', $e->getMessage());
            $this->assertStringContainsString('Syntax error', $e->getMessage());
        }
    }

    protected function getSpecification(string $path): DatadirTestSpecificationInterface
    {
        $datadirTestsFromDirectoryProvider = new DatadirTestsFromDirectoryProvider(__DIR__ . '/../functional/' . $path);
        $data = $datadirTestsFromDirectoryProvider();
        return $data['functional'][0];
    }

    protected function getTestCase(string $path): DatadirTestCase
    {
        $datadirTestsFromDirectoryProvider = new DatadirTestsFromDirectoryProvider(__DIR__ . '/../functional/' . $path);
        $datadirTestsFromDirectoryProvider();
        return new class ('testDatadir') extends DatadirTestCase {
            protected function getScript(): string
            {
                return __DIR__ . '/../functional/dummy-app.php';
            }
        };
    }
}
