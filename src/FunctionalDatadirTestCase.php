<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

/**
 * Test case for functional testing that stores test data internally.
 * This is used to programmatically run tests with specific specifications.
 *
 * @internal
 * @codeCoverageIgnore
 */
class FunctionalDatadirTestCase extends AbstractDatadirTestCase
{
    private ?DatadirTestSpecificationInterface $testData = null;

    /**
     * @param non-empty-string $name
     */
    public static function createWithData(
        string $name,
        DatadirTestSpecificationInterface $data,
    ): self {
        $instance = new self($name);
        $instance->testData = $data;
        return $instance;
    }

    public function initializeForTest(): void
    {
        $this->setUp();
    }

    /**
     * Run the test with the stored specification.
     * This method does not use @dataProvider - the data is set via createWithData().
     */
    public function testDatadir(): void
    {
        if ($this->testData === null) {
            $this->markTestSkipped('No test data provided - use createWithData()');
        }
        $specification = $this->testData;
        $tempDatadir = $this->getTempDatadir($specification);
        $process = $this->runScript($tempDatadir->getTmpFolder());
        $this->assertMatchesSpecification($specification, $process, $tempDatadir->getTmpFolder());
    }

    protected function getScript(): string
    {
        return __DIR__ . '/../tests/functional/dummy-app.php';
    }
}
