<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Aoeng\Laravel\Web3\Utils;
use phpseclib3\Math\BigInteger;

class IntegerType extends Type implements SolidityTypeInterface
{

    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat()
    {
        return $this->integerFormat($this->value);
    }

    /**
     * outputFormat
     *
     * @return string
     */
    public function outputFormat()
    {
        $match = [];

        $value = $this->value;

        if (preg_match('/^[0]+([a-f0-9]+)$/', $value, $match) === 1) {
            // due to value without 0x prefix, we will parse as decimal
            $value = '0x' . $match[1];
        }

        return Utils::toBn($value)->toString();
    }
}
