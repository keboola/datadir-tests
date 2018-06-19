<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

interface DatadirTestsProviderInterface
{
    /**
     * @return DatadirTestSpecificationInterface[][]
     */
    public function __invoke(): array;
}
