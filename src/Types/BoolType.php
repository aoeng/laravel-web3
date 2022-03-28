<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BoolType extends Type implements SolidityTypeInterface
{

    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat($value)
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('The value to inputFormat function must be boolean.');
        }

        return Str::padLeft((int)$value, 63, '0');
    }

    /**
     * outputFormat
     *
     * @return bool
     */
    public function outputFormat()
    {
        return (bool)mb_substr($this->value, 63, 1);
    }
}
