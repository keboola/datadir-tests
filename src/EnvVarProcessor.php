<?php

declare(strict_types=1);

namespace Keboola\DatadirTests;

use Keboola\DatadirTests\Exception\EnvVariableNotFoundException;
use Keboola\DatadirTests\Exception\UnexpectedTypeException;

class EnvVarProcessor
{
    /**
     * @return mixed
     */
    public function replaceEnv(string $value)
    {
        $pattern =
            '~^' .                       // start
            '%env\(' .                   // %env(
            '(?P<type>[a-z0-9_\-]+):' .  // TYPE:
            '(?P<var>[a-z0-9_\-]+)' .    // VAR
            '\)%' .                      // )%
            '$~i';                       // end

        if (preg_match($pattern, $value, $m)) {
            $var = $m['var'];
            $value = $this->getEnv($var);
            $value = $this->convertTo($m['type'], $var, $value);
        }

        return $value;
    }

    protected function getEnv(string $var): string
    {
        $value = getenv($var);
        if ($value === false) {
            throw new EnvVariableNotFoundException(sprintf('Env variable "%s" not found.', $var));
        }

        return $value;
    }

    /**
     * @return mixed
     */
    protected function convertTo(string $type, string $var, string $value)
    {
        switch ($type) {
            case 'string':
                return $value;
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'bool':
                return self::stringToBool($value);
        }

        throw new UnexpectedTypeException(sprintf('Unexpected type "%s" of the env variable "%s".', $type, $var));
    }

    protected static function stringToBool(string $value): bool
    {
        $value = strtolower($value);
        return
            $value !== '' &&
            $value !== 'f' &&
            $value !== 'false' &&
            $value !== 'no' &&
            $value !== '0';
    }
}
