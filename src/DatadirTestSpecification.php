<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

class DatadirTestSpecification implements DatadirTestSpecificationInterface
{
    public function __construct(
        private ?string $sourceDatadirDirectory = null,
        private ?int $expectedReturnCode = null,
        private ?string $expectedStdout = null,
        private ?string $expectedStderr = null,
        private ?string $expectedOutDirectory = null,
    ) {
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
