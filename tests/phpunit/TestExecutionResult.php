<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Helper class to track test execution results in PHPUnit 10+.
 *
 * PHPUnit 10+ removed the programmatic test running API (BaseTestRunner::STATUS_*,
 * TestCase::run() returning TestResult, TestResult::errorCount()/failureCount()/skippedCount()).
 * This class provides equivalent functionality by tracking test execution results.
 */
class TestExecutionResult
{
    public const STATUS_PASSED = 0;
    public const STATUS_FAILURE = 1;
    public const STATUS_ERROR = 2;

    private int $status;
    private int $errorCount = 0;
    private int $failureCount = 0;
    private int $skippedCount = 0;
    private ?Throwable $exception = null;

    private function __construct(int $status)
    {
        $this->status = $status;
    }

    /**
     * Run a test method and capture the result.
     *
     * @param callable $testMethod The test method to run (e.g., fn() => $test->testDatadir($spec))
     */
    public static function runTest(callable $testMethod): self
    {
        try {
            $testMethod();
            $result = new self(self::STATUS_PASSED);
        } catch (ExpectationFailedException $e) {
            $result = new self(self::STATUS_FAILURE);
            $result->failureCount = 1;
            $result->exception = $e;
        } catch (Throwable $e) {
            $result = new self(self::STATUS_ERROR);
            $result->errorCount = 1;
            $result->exception = $e;
        }

        return $result;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function errorCount(): int
    {
        return $this->errorCount;
    }

    public function failureCount(): int
    {
        return $this->failureCount;
    }

    public function skippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function getExceptionMessage(): string
    {
        return $this->exception !== null ? $this->exception->getMessage() : '';
    }

    /**
     * Assert that the test passed (equivalent to BaseTestRunner::STATUS_PASSED).
     */
    public function assertPassed(TestCase $testCase): void
    {
        $testCase->assertEquals(self::STATUS_PASSED, $this->status, 'Test should have passed');
        $testCase->assertEquals(0, $this->errorCount, 'Error count should be 0');
        $testCase->assertEquals(0, $this->failureCount, 'Failure count should be 0');
        $testCase->assertEquals(0, $this->skippedCount, 'Skipped count should be 0');
    }

    /**
     * Assert that the test failed (equivalent to BaseTestRunner::STATUS_FAILURE).
     */
    public function assertFailed(TestCase $testCase): void
    {
        $testCase->assertEquals(self::STATUS_FAILURE, $this->status, 'Test should have failed');
        $testCase->assertEquals(0, $this->errorCount, 'Error count should be 0');
        $testCase->assertEquals(1, $this->failureCount, 'Failure count should be 1');
        $testCase->assertEquals(0, $this->skippedCount, 'Skipped count should be 0');
    }

    /**
     * Assert that the test errored (equivalent to BaseTestRunner::STATUS_ERROR).
     */
    public function assertErrored(TestCase $testCase): void
    {
        $testCase->assertEquals(self::STATUS_ERROR, $this->status, 'Test should have errored');
        $testCase->assertEquals(1, $this->errorCount, 'Error count should be 1');
        $testCase->assertEquals(0, $this->failureCount, 'Failure count should be 0');
        $testCase->assertEquals(0, $this->skippedCount, 'Skipped count should be 0');
    }
}
