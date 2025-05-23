<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use InvalidArgumentException;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestsFromDirectoryProvider;
use LogicException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Runner\BaseTestRunner;

class DatadirTestCaseTest extends TestCase
{
    public function testExpectedSuccess(): void
    {
        $test = $this->getTestCase('001-successful');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(0, $result->failureCount());

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }
    public function testExpectedFail(): void
    {
        $test = $this->getTestCase('002-expected-fail');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(0, $result->failureCount());

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }
    public function testUnexpectedFailure(): void
    {

        $test = $this->getTestCase('003-unexpected-failure');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(1, $result->failureCount());
        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $this->assertStringContainsString('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertStringContainsString('-0', $failure->getExceptionAsString(), 'Expected code was not 0');
        $this->assertStringContainsString('+2', $failure->getExceptionAsString(), 'Actal code was not 2');

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }
    public function testUnexpectedSuccess(): void
    {
        $test = $this->getTestCase('004-unexpected-success');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());

        $this->assertEquals(0, $result->errorCount());

        $this->assertEquals(1, $result->failureCount());
        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        $failure = $failures[0];
        $this->assertStringContainsString('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertStringContainsString('-1', $failure->getExceptionAsString(), 'Expected should be 1');
        $this->assertStringContainsString('+0', $failure->getExceptionAsString(), 'Actual should be 0');

        $this->assertEquals(0, $result->skippedCount());

        $this->assertCount(1, $result);
    }

    public function testExpectedUserError(): void
    {
        $test = $this->getTestCase('005-expected-user-error');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedInternalError(): void
    {
        $test = $this->getTestCase('006-expected-internal-error');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedUserErrorWithOutputFolder(): void
    {
        $test = $this->getTestCase('007-expected-user-error-with-output-folder');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedInternalError(): void
    {
        $test = $this->getTestCase('008-unexpected-internal-error-instead-of-user-error');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        /** @var TestFailure $failure */
        $failure = $failures[0];
        $this->assertStringContainsString('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertStringContainsString(
            '-1',
            $failure->getExceptionAsString(),
            'Expected exit code should have been 1',
        );
        $this->assertStringContainsString(
            '+2',
            $failure->getExceptionAsString(),
            'Actual exit code should have been 2',
        );

        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedUserError(): void
    {
        $test = $this->getTestCase('009-unexpected-user-error-instead-of-internal-error');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        /** @var TestFailure $failure */
        $failure = $failures[0];
        $this->assertStringContainsString('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertStringContainsString(
            '-2',
            $failure->getExceptionAsString(),
            'Expected exit code should have been 2',
        );
        $this->assertStringContainsString(
            '+1',
            $failure->getExceptionAsString(),
            'Actual exit code should have been 1',
        );

        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testUnexpectedSuccessWithExplicitlyExpectedError(): void
    {
        $test = $this->getTestCase('011-unexpected-success-with-explicitly-expected-exit-code');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        /** @var TestFailure $failure */
        $failure = $failures[0];
        $this->assertStringContainsString('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertStringContainsString(
            '-1',
            $failure->getExceptionAsString(),
            'Expected exit code should have been 1',
        );
        $this->assertStringContainsString(
            '+0',
            $failure->getExceptionAsString(),
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
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedStdoutNotMatch(): void
    {
        $test = $this->getTestCase('014-expected-stdout-not-match');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);

        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        /** @var TestFailure $failure */
        $failure = $failures[0];
        $error = $failure->getExceptionAsString();
        $this->assertStringContainsString('Failed asserting stdout output', $error);
        $this->assertStringContainsString('Failed asserting that string matches format description', $error);
        $this->assertStringContainsString("-another message\n", $error);
        $this->assertStringContainsString("+stdout message '12345'\n", $error);
    }

    public function testExpectedStderrMatch(): void
    {
        $test = $this->getTestCase('015-expected-stderr-match');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExpectedStderrNotMatch(): void
    {
        $test = $this->getTestCase('016-expected-stderr-not-match');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);

        /** @var TestFailure[] $failures */
        $failures = $result->failures();
        /** @var TestFailure $failure */
        $failure = $failures[0];
        $error = $failure->getExceptionAsString();
        $this->assertStringContainsString('Failed asserting stderr output', $error);
        $this->assertStringContainsString('Failed asserting that string matches format description', $error);
        $this->assertStringContainsString("-another message\n", $error);
        $this->assertStringContainsString("+stderr message '12345'\n", $error);
    }

    public function testModifyConfig(): void
    {
        putenv('MY_TEST_VAR_123=some simple message');
        $test = $this->getTestCase('017-modify-config');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testInvalidConfig(): void
    {
        $test = $this->getTestCase('018-invalid-config');
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_ERROR, $test->getStatus());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());

        $errors = $result->errors();
        $error = $errors[0];
        $error = $error->getExceptionAsString();
        $this->assertStringContainsString(
            'Keboola\DatadirTests\Exception\DatadirTestsException: ' .
            'Cannot decode "config.json", dataset "functional": Syntax error',
            $error,
        );
    }

    protected function getTestCase(string $path): DatadirTestCase
    {
        $datadirTestsFromDirectoryProvider = new DatadirTestsFromDirectoryProvider(__DIR__ . '/../functional/' . $path);
        $data = $datadirTestsFromDirectoryProvider();
        return new class('testDatadir', $data['functional'], 'functional') extends DatadirTestCase
        {
            protected function getScript(): string
            {
                return __DIR__ . '/../functional/dummy-app.php';
            }
        };
    }
}
