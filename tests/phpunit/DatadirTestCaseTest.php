<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use InvalidArgumentException;
use Keboola\DatadirTests\DatadirTestsFromDirectoryProvider;
use Keboola\DatadirTests\DatadirTestSpecificationInterface;
use Keboola\DatadirTests\FunctionalDatadirTestCase;
use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Throwable;

class DatadirTestCaseTest extends TestCase
{
    public function testExpectedSuccess(): void
    {
        $test = $this->getTestCase('001-successful');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(0, $result->failureCount());

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }

    public function testExpectedFail(): void
    {
        $test = $this->getTestCase('002-expected-fail');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(0, $result->failureCount());

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }

    public function testUnexpectedFailure(): void
    {
        $test = $this->getTestCase('003-unexpected-failure');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(1, $result->failureCount());
        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting exit code', $failureString);
        $this->assertStringContainsString('-0', $failureString, 'Expected code was not 0');
        $this->assertStringContainsString('+2', $failureString, 'Actual code was not 2');

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }

    public function testUnexpectedSuccess(): void
    {
        $test = $this->getTestCase('004-unexpected-success');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(1, $result->failureCount());
        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting exit code', $failureString);
        $this->assertStringContainsString('-1', $failureString, 'Expected should be 1');
        $this->assertStringContainsString('+0', $failureString, 'Actual should be 0');

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }

    public function testExpectedUserError(): void
    {
        $test = $this->getTestCase('005-expected-user-error');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedInternalError(): void
    {
        $test = $this->getTestCase('006-expected-internal-error');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedUserErrorWithOutputFolder(): void
    {
        $test = $this->getTestCase('007-expected-user-error-with-output-folder');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedInternalError(): void
    {
        $test = $this->getTestCase('008-unexpected-internal-error-instead-of-user-error');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting exit code', $failureString);
        $this->assertStringContainsString(
            '-1',
            $failureString,
            'Expected exit code should have been 1',
        );
        $this->assertStringContainsString(
            '+2',
            $failureString,
            'Actual exit code should have been 2',
        );

        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedUserError(): void
    {
        $test = $this->getTestCase('009-unexpected-user-error-instead-of-internal-error');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting exit code', $failureString);
        $this->assertStringContainsString(
            '-2',
            $failureString,
            'Expected exit code should have been 2',
        );
        $this->assertStringContainsString(
            '+1',
            $failureString,
            'Actual exit code should have been 1',
        );

        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedSuccessWithExplicitlyExpectedError(): void
    {
        $test = $this->getTestCase('011-unexpected-success-with-explicitly-expected-exit-code');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting exit code', $failureString);
        $this->assertStringContainsString(
            '-1',
            $failureString,
            'Expected exit code should have been 1',
        );
        $this->assertStringContainsString(
            '+0',
            $failureString,
            'Actual exit code should have been 0',
        );

        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
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
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedStdoutNotMatch(): void
    {
        $test = $this->getTestCase('014-expected-stdout-not-match');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);

        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting stdout output', $failureString);
        $this->assertStringContainsString('Failed asserting that string matches format description', $failureString);
        $this->assertStringContainsString("-another message\n", $failureString);
        $this->assertStringContainsString("+stdout message '12345'\n", $failureString);
    }

    public function testExpectedStderrMatch(): void
    {
        $test = $this->getTestCase('015-expected-stderr-match');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedStderrNotMatch(): void
    {
        $test = $this->getTestCase('016-expected-stderr-not-match');
        $result = $this->runTest($test);

        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);

        /** @var Throwable[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $failureString = $this->getFailureAsString($failure);
        $this->assertStringContainsString('Failed asserting stderr output', $failureString);
        $this->assertStringContainsString('Failed asserting that string matches format description', $failureString);
        $this->assertStringContainsString("-another message\n", $failureString);
        $this->assertStringContainsString("+stderr message '12345'\n", $failureString);
    }

    public function testModifyConfig(): void
    {
        putenv('MY_TEST_VAR_123=some simple message');
        $test = $this->getTestCase('017-modify-config');
        $result = $this->runTest($test);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testInvalidConfig(): void
    {
        $test = $this->getTestCase('018-invalid-config');
        $result = $this->runTest($test);

        $this->assertTrue($result->isError());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());

        $errors = $result->errors();
        $error = $errors[0];
        $errorString = (string) $error;
        $this->assertStringContainsString(
            'Keboola\DatadirTests\Exception\DatadirTestsException',
            $errorString,
        );
        $this->assertStringContainsString('Cannot decode "config.json"', $errorString);
        $this->assertStringContainsString('Syntax error', $errorString);
    }

    protected function runTest(FunctionalDatadirTestCase $test): TestResultCollector
    {
        $result = new TestResultCollector();
        $result->run($test);
        return $result;
    }

    protected function getTestCase(string $path): FunctionalDatadirTestCase
    {
        $datadirTestsFromDirectoryProvider = new DatadirTestsFromDirectoryProvider(__DIR__ . '/../functional/' . $path);
        $data = $datadirTestsFromDirectoryProvider();

        return FunctionalDatadirTestCase::createWithData('testDatadir', $data['functional'][0]);
    }

    /**
     * Get the full failure message including the comparison diff.
     */
    protected function getFailureAsString(Throwable $failure): string
    {
        $message = $failure->getMessage();
        if ($failure instanceof ExpectationFailedException && $failure->getComparisonFailure() !== null) {
            $message .= "\n" . $failure->getComparisonFailure()->getDiff();
        }
        return $message;
    }
}
