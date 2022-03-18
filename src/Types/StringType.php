<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Contracts\SolidityTypeInterface;
use Aoeng\Laravel\Web3\Utils;

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
    public function inputFormat()
    {
        $value = Utils::toHex($this->value);
        $prefix = $this->integerFormat(mb_strlen($value) / 2);
        $l = floor((mb_strlen($value) + 63) / 64);
        $padding = (($l * 64 - mb_strlen($value) + 1) >= 0) ? $l * 64 - mb_strlen($value) : 0;

        return $prefix . $value . implode('', array_fill(0, $padding, '0'));
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
