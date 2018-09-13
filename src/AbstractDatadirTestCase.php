<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

use Keboola\DatadirTests\Exception\DatadirTestsException;
use Keboola\Temp\Temp;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class AbstractDatadirTestCase extends TestCase
{
    /** @var string */
    protected $testFileDir;

    public function __construct(
        ?string $name = null,
        array $data = [],
        string $dataName = ''
    ) {
        $reflectionClass = new ReflectionClass(static::class);
        $this->testFileDir = dirname($reflectionClass->getFileName());
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return DatadirTestSpecificationInterface[][]
     */
    public function provideDatadirSpecifications(): array
    {
        $data = [[]];
        foreach ($this->getDataProviders() as $provider) {
            $data[] = $provider();
        }
        // https://github.com/kalessil/phpinspectionsea/blob/master/docs/performance.md#slow-array-function-used-in-loop
        return array_merge(...$data);
    }

    /**
     * @return DatadirTestsProviderInterface[]
     */
    protected function getDataProviders(): array
    {
        return [
            new DatadirTestsFromDirectoryProvider($this->getTestFileDir()),
        ];
    }

    public function getTestFileDir(): string
    {
        return $this->testFileDir;
    }

    public function assertDirectoryContentsSame(string $expected, string $actual): void
    {
        $fs = new Filesystem();
        if (!$fs->exists($expected)) {
            throw new AssertionFailedError(sprintf(
                'Expected path "%s" does not exist',
                $expected
            ));
        }
        if (!$fs->exists($actual)) {
            throw new AssertionFailedError(sprintf(
                'Actual path "%s" does not exist',
                $actual
            ));
        }
        $expected = realpath($expected);
        $actual = realpath($actual);
        $diffCommand = [
            'diff',
            '--exclude=.gitkeep',
            '--ignore-all-space',
            '--recursive',
            $expected,
            $actual,
        ];
        $diffProcess = new Process($diffCommand);
        $diffProcess->run();
        if ($diffProcess->getExitCode() > 0) {
            throw new AssertionFailedError(sprintf(
                'Two directories are not the same:' . \PHP_EOL .
                '%s' . \PHP_EOL .
                '%s' . \PHP_EOL .
                '%s' . \PHP_EOL .
                '%s',
                $expected,
                $actual,
                $diffProcess->getOutput(),
                $diffProcess->getErrorOutput()
            ));
        }
    }

    protected function getScript(): string
    {
        return $this->getTestFileDir() . '/../../src/run.php';
    }

    protected function getTempDatadir(DatadirTestSpecificationInterface $specification): Temp
    {
        $fs = new Filesystem();

        $temp = new Temp();
        $temp->initRunFolder();

        if ($specification->getSourceDatadirDirectory() !== null) {
            $fs->mirror($specification->getSourceDatadirDirectory(), $temp->getTmpFolder());
        }

        $fs->mkdir($temp->getTmpFolder() . '/in/tables', 0777);
        $fs->mkdir($temp->getTmpFolder() . '/in/files', 0777);
        $fs->mkdir($temp->getTmpFolder() . '/out/tables', 0777);
        $fs->mkdir($temp->getTmpFolder() . '/out/files', 0777);
        return $temp;
    }

    protected function assertMatchesSpecification(
        DatadirTestSpecificationInterface $specification,
        Process $runProcess,
        string $tempDatadir
    ): void {
        if ($specification->getExpectedReturnCode() !== null) {
            $this->assertProcessReturnCode($specification->getExpectedReturnCode(), $runProcess);
        } else {
            $this->assertNotSame(0, $runProcess->getExitCode(), 'Exit code should have been non-zero');
        }
        if ($specification->getExpectedStdout() !== null) {
            $this->assertSame(
                $specification->getExpectedStdout(),
                $runProcess->getOutput(),
                'Failed asserting stdout output'
            );
        }
        if ($specification->getExpectedStderr() !== null) {
            $this->assertSame(
                $specification->getExpectedStderr(),
                $runProcess->getErrorOutput(),
                'Failed asserting stderr output'
            );
        }
        if ($specification->getExpectedOutDirectory() !== null) {
            $this->assertDirectoryContentsSame(
                $specification->getExpectedOutDirectory(),
                $tempDatadir . '/out'
            );
        }
    }

    protected function assertProcessReturnCode(int $expectedReturnCode, Process $runProcess): void
    {
        $exitCode = $runProcess->getExitCode();
        if ($exitCode == $expectedReturnCode) {
            $this->assertSame($expectedReturnCode, $exitCode);
            return;
        }

        $message = '';

        if ($runProcess->getOutput()) {
            $message .= "\nOutput:\n";
            $message .= $runProcess->getOutput();
        }
        if ($runProcess->getErrorOutput()) {
            $message .= "\nError output:\n";
            $message .= $runProcess->getErrorOutput();
        }
        throw new ExpectationFailedException(
            'Failed asserting exit code' . PHP_EOL . $message,
            new ComparisonFailure($expectedReturnCode, $exitCode, (string) $expectedReturnCode, (string) $exitCode)
        );
    }

    protected function runCommonTest(
        string $testDirectory,
        array $configuration,
        int $expectedReturnCode,
        ?string $expectedStdout,
        ?string $expectedStderr
    ): void {
        $specification = new DatadirTestSpecification(
            $testDirectory . '/source/data',
            $expectedReturnCode,
            $expectedStdout,
            $expectedStderr,
            $testDirectory . '/expected/data/out'
        );

        $tempFolder = $this->getTempDatadir($specification)->getTmpFolder();
        file_put_contents(
            $tempFolder . '/config.json',
            json_encode($configuration, JSON_PRETTY_PRINT)
        );
        $process = $this->runScript($tempFolder);
        $this->assertMatchesSpecification($specification, $process, $tempFolder);
    }

    protected function runScript(string $datadirPath): Process
    {
        $fs = new Filesystem();

        $script = $this->getScript();
        if (!$fs->exists($script)) {
            throw new DatadirTestsException(sprintf(
                'Cannot open script file "%s"',
                $script
            ));
        }

        $runCommand = [
            "php",
            $script,
        ];
        $runProcess = new Process($runCommand);
        $runProcess->setEnv([
            'KBC_DATADIR' => $datadirPath,
        ]);
        $runProcess->run();
        return $runProcess;
    }
}
