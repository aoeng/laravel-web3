<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Aoeng\Laravel\Web3\Utils;
use InvalidArgumentException;

class AddressType extends Type implements SolidityTypeInterface
{


    /**
     * inputFormat
     * to do: iban
     *
     * @return string
     * @throws \Exception
     */
    public function inputFormat($value)
    {
        return Utils::integerFormat(Utils::stripZero(mb_strtolower($value)));
    }


    /**
     * outputFormat
     *
     * @return string
     */
    public function outputFormat()
    {
        return '0x' . mb_substr($this->value, 24, 40);
    }
}
