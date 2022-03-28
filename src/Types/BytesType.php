<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Aoeng\Laravel\Web3\Utils;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BytesType extends Type implements SolidityTypeInterface
{

    public function isDynamicType()
    {
        if ($this->type == 'bytes') {
            return true;
        }

        if (Str::contains($this->type, '[]')) {
            return true;
        }

        if (Str::startsWith($this->type, 'bytes[')) {
            return true;
        }

        if (Str::contains($this->type, '(')) {
            return true;
        }

        return false;
    }


    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat($value)
    {

        $value = Utils::stripZero($value);

        if (mb_strlen($value) % 2 !== 0) {
            $value = "0" . $value;
        }

        return Str::padRight($value, 64, '0');
    }


    /**
     * outputFormat
     *
     * @return string
     */
    public function outputFormat()
    {
        $checkZero = str_replace('0', '', $this->value);

        if (empty($checkZero)) {
            return '0';
        }

//        $value = mb_substr($this->value, 0, 64);

        return '0x' . $this->value;
    }
}
