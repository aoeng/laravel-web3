<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use InvalidArgumentException;

class BoolType extends Type implements SolidityTypeInterface
{

    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat()
    {
        if (!is_bool($this->value)) {
            throw new InvalidArgumentException('The value to inputFormat function must be boolean.');
        }

        return '000000000000000000000000000000000000000000000000000000000000000' . ((int)$this->value);
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
