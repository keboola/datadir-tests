<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use Countable;
use Keboola\DatadirTests\FunctionalDatadirTestCase;
use PHPUnit\Framework\AssertionFailedError;
use Throwable;

/**
 * Helper class to collect test results in PHPUnit 10+ style.
 * Mimics the old TestResult API for backwards compatibility in tests.
 */
class TestResultCollector implements Countable
{
    /** @var int<0, max> */
    private int $errorCount = 0;
    /** @var int<0, max> */
    private int $failureCount = 0;
    /** @var int<0, max> */
    private int $skippedCount = 0;
    /** @var int<0, max> */
    private int $testCount = 0;

    /** @var array<int, Throwable> */
    private array $failures = [];

    /** @var array<int, Throwable> */
    private array $errors = [];

    private bool $isSuccess = false;
    private bool $isFailure = false;
    private bool $isError = false;

    public function run(FunctionalDatadirTestCase $test): void
    {
        $this->testCount = 1;
        $test->initializeForTest();

        try {
            // Get the test method name from the test
            $testMethod = $test->name();
            $test->$testMethod();
            $this->isSuccess = true;
        } catch (AssertionFailedError $e) {
            $this->isFailure = true;
            $this->failureCount = 1;
            $this->failures[] = $e;
        } catch (Throwable $e) {
            $this->isError = true;
            $this->errorCount = 1;
            $this->errors[] = $e;
        }
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

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->testCount;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function isFailure(): bool
    {
        return $this->isFailure;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    /**
     * @return array<int, Throwable>
     */
    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * @return array<int, Throwable>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
