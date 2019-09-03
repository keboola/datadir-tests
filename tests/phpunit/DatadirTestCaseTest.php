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

        /** @var TestFailure[] $errors */
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

        /** @var TestFailure[] $errors */
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
        $this->assertContains('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertContains('-0', $failure->getExceptionAsString(), 'Expected code was not 0');
        $this->assertContains('+2', $failure->getExceptionAsString(), 'Actal code was not 2');

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
        $this->assertContains('Exit code should have been non-zero', $failure->exceptionMessage());
        $this->assertContains('Failed asserting that 0 is not identical to 0', $failure->exceptionMessage());

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
        $this->assertContains('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertContains('-1', $failure->getExceptionAsString(), 'Expected exit code should have been 1');
        $this->assertContains('+2', $failure->getExceptionAsString(), 'Actual exit code should have been 2');

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
        $this->assertContains('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertContains('-2', $failure->getExceptionAsString(), 'Expected exit code should have been 2');
        $this->assertContains('+1', $failure->getExceptionAsString(), 'Actual exit code should have been 1');

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
        $this->assertContains('Failed asserting exit code', $failure->exceptionMessage());
        $this->assertContains('-1', $failure->getExceptionAsString(), 'Expected exit code should have been 1');
        $this->assertContains('+0', $failure->getExceptionAsString(), 'Actual exit code should have been 0');

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
        $this->expectExceptionMessage('functional: At least one of "expected/out/data" folder or "expected-code" file must exist');
        $this->getTestCase('012-neither-code-or-folder');
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
