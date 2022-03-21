<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Utils;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Type
{

    protected $value = null;

    /**
     * construct
     *
     * @return void
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }


    /**
     * isDynamicType
     *
     * @return bool
     */
    public function isDynamicType()
    {
        return false;
    }

    /**
     * nestedTypes
     * 判断方法是否数组 返回 false | [0=>'[1]']
     * @param string $name
     * @return mixed
     */
    public function nestedTypes($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('nestedTypes name must string.');
        }
        $matches = [];

        if (preg_match_all('/(\[[0-9]*\])/', $name, $matches, PREG_PATTERN_ORDER) >= 1) {
            return $matches[0];
        }
        return false;
    }


    /**
     * isDynamicArray
     *  判断是不固定长度的数组
     *
     *
     * @param string $name
     * @return bool
     */
    public function isDynamicArray($name)
    {
        $nestedTypes = $this->nestedTypes($name);

        return $nestedTypes && preg_match('/[0-9]{1,}/', $nestedTypes[count($nestedTypes) - 1]) !== 1;
    }

    /**
     * isStaticArray
     * 判断是固定长度的数组
     *
     * @param string $name
     * @return bool
     */
    public function isStaticArray($name)
    {
        $nestedTypes = $this->nestedTypes($name);

        return $nestedTypes && preg_match('/[0-9]{1,}/', $nestedTypes[count($nestedTypes) - 1]) === 1;
    }


    /**
     * encode
     *
     * @return string
     */
    public function encode()
    {
        // TODO:: 数组类型

        return $this->inputFormat();
    }


    public function decode()
    {
        // TODO:: 数组类型

        return $this->outputFormat();
    }


    /**
     * staticArrayLength
     *  静态数组的长度
     *
     * @param string $name
     * @return int
     */
    public function staticArrayLength($name)
    {
        $nestedTypes = $this->nestedTypes($name);

        if ($nestedTypes === false) {
            return 1;
        }
        $match = [];

        if (preg_match('/[0-9]{1,}/', $nestedTypes[count($nestedTypes) - 1], $match) === 1) {
            return (int)$match[0];
        }
        return 1;
    }

    /**
     * staticPartLength
     *
     * 总长度 * 32
     *
     * @param string $name
     * @return int
     */
    public function staticPartLength($name)
    {
        $nestedTypes = $this->nestedTypes($name);

        if ($nestedTypes === false) {
            $nestedTypes = ['[1]'];
        }
        $count = 32;

        foreach ($nestedTypes as $type) {
            $num = mb_substr($type, 1, 1);

            if (!is_numeric($num)) {
                $num = 1;
            } else {
                $num = intval($num);
            }
            $count *= $num;
        }

        return $count;
    }


    public function integerFormat($value, $digit = 64)
    {
        $hex = Utils::toBn($value)->toHex(true);
        $padded = mb_substr($hex, 0, 1);

        if ($padded !== 'f') {
            $padded = '0';
        }

        return implode('', array_fill(0, $digit - mb_strlen($hex), $padded)) . $hex;
    }

}
