<?php

namespace Aoeng\Laravel\Web3\Types;

use Aoeng\Laravel\Web3\Utils;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Type
{

    protected $value = null;

    protected $type = null;

    /**
     * construct
     *
     * @return void
     */
    public function __construct($value = null, $type = null)
    {
        if (Str::contains($type, '[') && !is_array($value)) {
            throw new InvalidArgumentException($type . ': must array.');
        }

        $this->value = $value;
        $this->type = $type;

    }


    /**
     *  动态数据,输入encode不同
     *  https://docs.soliditylang.org/en/develop/abi-spec.html
     *
     * @return bool
     */
    public function isDynamicType()
    {
        if (Str::contains($this->type, '[]')) {
            return true;
        }

        return false;
    }


    public function length()
    {
        if (is_array($this->value)) {
            $length = count($this->value);
        } else {
            $length = mb_strlen(Utils::stripZero($this->value));
        }

        return Utils::integerFormat($length);
    }

    /**
     * encode
     *
     * @return array
     */
    public function encode()
    {
        $value = '';

        if (Str::contains($this->type, '[')) {
            foreach ($this->value as $item) {
                $value .= $this->inputFormat($item);
            }
        } else {
            $value = $this->inputFormat($this->value);
        }

        if ($this->isDynamicType()) {
            $value = $this->length() . $value;
        }

        return ['value' => $value, 'dynamic' => $this->isDynamicType(), 'length' => floor(mb_strlen($value) / 2)];
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
}
