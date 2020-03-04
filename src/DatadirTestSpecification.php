<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

class DatadirTestSpecification implements DatadirTestSpecificationInterface
{
    private ?string $sourceDatadirDirectory;

    private ?string $expectedStdout;

    private ?string $expectedStderr;

    private ?int $expectedReturnCode;

    private ?string $expectedOutDirectory;

    public function __construct(
        ?string $sourceDatadirDirectory = null,
        ?int $expectedReturnCode = null,
        ?string $expectedStdout = null,
        ?string $expectedStderr = null,
        ?string $expectedOutDirectory = null
    ) {
        $this->sourceDatadirDirectory = $sourceDatadirDirectory;
        $this->expectedReturnCode = $expectedReturnCode;
        $this->expectedStdout = $expectedStdout;
        $this->expectedStderr = $expectedStderr;
        $this->expectedOutDirectory = $expectedOutDirectory;
    }

    public function getSourceDatadirDirectory(): ?string
    {
        return $this->sourceDatadirDirectory;
    }

    public function getExpectedReturnCode(): ?int
    {
        return $this->expectedReturnCode;
    }

    public function getExpectedStdout(): ?string
    {
        return $this->expectedStdout;
    }

    public function getExpectedStderr(): ?string
    {
        return $this->expectedStderr;
    }

    public function getExpectedOutDirectory(): ?string
    {
        return $this->expectedOutDirectory;
    }
}
