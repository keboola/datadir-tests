<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

interface DatadirTestSpecificationInterface
{
    public function getSourceDatadirDirectory(): ?string;

    public function getExpectedReturnCode(): ?int;

    public function getExpectedStdout(): ?string;

    public function getExpectedStderr(): ?string;

    public function getExpectedOutDirectory(): ?string;
}
