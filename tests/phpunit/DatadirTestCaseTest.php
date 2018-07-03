<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestsFromDirectoryProvider;
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
