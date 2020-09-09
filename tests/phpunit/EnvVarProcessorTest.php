<?php

declare(strict_types=1);

namespace Keboola\DatadirTests\Tests;

use Keboola\DatadirTests\EnvVarProcessor;
use Keboola\DatadirTests\Exception\EnvVariableNotFoundException;
use Keboola\DatadirTests\Exception\UnexpectedTypeException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class EnvVarProcessorTest extends TestCase
{
    /**
     * @dataProvider getValidInputs
     * @param mixed $expected
     */
    public function testReplaceEnv(?string $var, ?string $value, string $statement, $expected): void
    {
        if ($var) {
            putenv("$var=$value");
        }

        $processor = new EnvVarProcessor();
        Assert::assertSame($expected, $processor->replaceEnv($statement));
    }

    public function getValidInputs(): array
    {
        return [
            'no_var_1' => [null, null, 'abc', 'abc'],
            'no_var_2' => [null, null, '123', '123'],
            'string-empty' => ['VAR1', '', '%env(string:VAR1)%', ''],
            'string-value' => ['VAR1', 'var 1 content', '%env(string:VAR1)%', 'var 1 content'],
            'int-empty' => ['VAR1', '', '%env(int:VAR1)%', 0],
            'int-string' => ['VAR1', 'content', '%env(int:VAR1)%', 0],
            'int-ok' => ['VAR1', '456', '%env(int:VAR1)%', 456],
            'float-empty' => ['MY_FLOAT_VAR_123', '', '%env(float:MY_FLOAT_VAR_123)%', 0.0],
            'float-string' => ['MY_FLOAT_VAR_123', 'content', '%env(float:MY_FLOAT_VAR_123)%', 0.0],
            'float-int' => ['VAR1', '456', '%env(float:VAR1)%', 456.0],
            'float-ok' => ['VAR1', '456.78', '%env(float:VAR1)%', 456.78],
            'bool-invalid' => ['VAR1', 'content', '%env(bool:VAR1)%', false],
            'bool-empty' => ['VAR1', '', '%env(bool:VAR1)%', false],
            'bool-yes' => ['VAR1', 'yes', '%env(bool:VAR1)%', true],
            'bool-no' => ['VAR1', 'no', '%env(bool:VAR1)%', false],
            'bool-YES' => ['VAR1', 'YES', '%env(bool:VAR1)%', true],
            'bool-NO' => ['VAR1', 'NO', '%env(bool:VAR1)%', false],
            'bool-true' => ['VAR1', 'true', '%env(bool:VAR1)%', true],
            'bool-false' => ['VAR1', 'false', '%env(bool:VAR1)%', false],
            'bool-TRUE' => ['VAR1', 'TRUE', '%env(bool:VAR1)%', true],
            'bool-FALSE' => ['VAR1', 'FALSE', '%env(bool:VAR1)%', false],
            'bool-t' => ['VAR1', 't', '%env(bool:VAR1)%', true],
            'bool-f' => ['VAR1', 'f', '%env(bool:VAR1)%', false],
            'bool-T' => ['VAR1', 'T', '%env(bool:VAR1)%', true],
            'bool-F' => ['VAR1', 'F', '%env(bool:VAR1)%', false],
            'bool-1' => ['VAR1', '1', '%env(bool:VAR1)%', true],
            'bool-0' => ['VAR1', '0', '%env(bool:VAR1)%', false],
        ];
    }

    public function testVarNotFound(): void
    {
        putenv('VAR1'); // unset variable
        $this->expectException(EnvVariableNotFoundException::class);
        $this->expectExceptionMessage('Env variable "VAR1" not found.');
        $processor = new EnvVarProcessor();
        $processor->replaceEnv('%env(string:VAR1)%');
    }

    public function testInvalidType(): void
    {
        putenv('VAR1=abc');
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Unexpected type "invalid" of the env variable "VAR1".');
        $processor = new EnvVarProcessor();
        $processor->replaceEnv('%env(invalid:VAR1)%');
    }
}
