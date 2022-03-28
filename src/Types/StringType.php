<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Aoeng\Laravel\Web3\Utils;
use Illuminate\Support\Str;

class StringType extends Type implements SolidityTypeInterface
{

    /**
     * isDynamicType
     *
     * @return bool
     */
    public function isDynamicType()
    {
        return true;
    }

    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat($value)
    {
        $value = Utils::toHex($value);

        return  Str::padRight($value, 64, '0');
    }

    /**
     * outputFormat
     *
     * @return string
     */
    public function outputFormat()
    {
        $strLen = mb_substr($this->value, 0, 64);
        $strValue = mb_substr($this->value, 64);

        if (preg_match('/^[0]+([a-f0-9]+)$/', $strLen, $match) === 1) {
            $strLen = Utils::toBn('0x' . $match[1])->toString();
        }

        $strValue = mb_substr($strValue, 0, (int)$strLen * 2);

        return hex2bin(Utils::stripZero($strValue));
    }
}
