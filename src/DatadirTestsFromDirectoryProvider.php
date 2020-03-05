<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

use Keboola\DatadirTests\Exception\DatadirTestsException;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DatadirTestsFromDirectoryProvider implements DatadirTestsProviderInterface
{
    /** @var string */
    private $testDirectory;

    /** @var DatadirTestSpecification[][] */
    private $datapoints;

    public function __construct(
        string $testDirectory = 'tests/functional'
    ) {
        $this->testDirectory = $testDirectory;
    }

    /**
     * @return DatadirTestSpecificationInterface[][]
     */
    public function __invoke(): array
    {
        $this->datapoints = [];
        $this->processDirectory($this->testDirectory);
        return $this->datapoints;
    }

    protected function getFoundDirectories(): Finder
    {
        $finder = new Finder();
        $finder->directories()
            ->sortByName()
            ->in($this->testDirectory)
            ->depth(0)
        ;
        return $finder;
    }

    protected function processDirectory(string $testDirectory): void
    {
        $fs = new Filesystem();
        if (!$fs->exists($testDirectory)) {
            throw new DatadirTestsException(sprintf(
                'Tests directory "%s" does not exist',
                $testDirectory
            ));
        }
        $finder = $this->getFoundDirectories();
        foreach ($finder as $testSuite) {
            $this->processOneTest($testSuite);
        }
    }

    protected function processOneTest(SplFileInfo $testSuite): void
    {
        $workingDirectory = $testSuite->getPathname();

        $name = $testSuite->getBasename();
        $sourceDatadirDirectory = $workingDirectory . '/source/data';
        $expectedStdout = null;
        $expectedStderr = null;
        $expectedReturnCodeFile = $workingDirectory . '/expected-code';
        $expectedReturnCode = null;
        $expectedOutputDirectory = null;
        $outTemplateDir = $workingDirectory . '/expected/data/out';

        if (file_exists($expectedReturnCodeFile)) {
            $returnCode = trim((string) file_get_contents($expectedReturnCodeFile));
            if (preg_match('~^[012]$~', $returnCode)) {
                $expectedReturnCode = (int) $returnCode;
            } else {
                throw new \InvalidArgumentException(sprintf(
                    '%s: Expecting invalid return code (%s). Possible codes are: 0, 1, 2.',
                    $name,
                    $returnCode
                ));
            }
        }

        if (file_exists($outTemplateDir)) {
            if (is_null($expectedReturnCode)) {
                $expectedReturnCode = 0;
            }
            $expectedOutputDirectory = $outTemplateDir;
        }

        if ($expectedOutputDirectory === null && $expectedReturnCode === null) {
            throw new LogicException(sprintf(
                '%s: At least one of "expected/out/data" folder or "expected-code" file must exist',
                $name
            ));
        }

        $this->datapoints[$name] = [
            new DatadirTestSpecification(
                $sourceDatadirDirectory,
                $expectedReturnCode,
                $expectedStdout,
                $expectedStderr,
                $expectedOutputDirectory
            ),
        ];
    }
}
