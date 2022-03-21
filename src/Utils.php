<?php

/**
 * This file is part of web3.php package.
 *
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Aoeng\Laravel\Web3;

use Exception;
use Illuminate\Support\Str;
use kornrunner\Keccak;
use InvalidArgumentException;
use phpseclib\Math\BigInteger;


class Utils
{
    /**
     * SHA3_NULL_HASH
     *
     * @const string
     */
    const SHA3_NULL_HASH = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';

    public static function isZeroPrefixed(string $value)
    {
        return Str::startsWith(Str::lower($value), '0x');
    }

    public static function isAddress(string $value)
    {
        return preg_match('/^(0x)?[a-f0-9]{40}$/', Str::lower($value)) === 1;
    }


    public static function fromHex($value, $decimal = 0)
    {
        return bcdiv(self::toBn($value)->toString(), pow(10, $decimal), $decimal);
    }


    public static function decodeTransaction($transaction)
    {
        if (empty($transaction)) {
            return [];
        }

        isset($transaction['blockNumber']) && $transaction['blockNumber'] = self::fromHex($transaction['blockNumber']);
        isset($transaction['gas']) && $transaction['gas'] = self::fromHex($transaction['gas']);
        isset($transaction['gasPrice']) && $transaction['gasPrice'] = self::fromHex($transaction['gasPrice'], 9);
        isset($transaction['nonce']) && $transaction['nonce'] = self::fromHex($transaction['nonce']);
        isset($transaction['transactionIndex']) && $transaction['transactionIndex'] = self::fromHex($transaction['transactionIndex']);
        isset($transaction['value']) && $transaction['value'] = self::fromHex($transaction['value']);
        isset($transaction['cumulativeGasUsed']) && $transaction['cumulativeGasUsed'] = self::fromHex($transaction['cumulativeGasUsed']);
        isset($transaction['gasUsed']) && $transaction['gasUsed'] = self::fromHex($transaction['gasUsed']);
        isset($transaction['status']) && $transaction['status'] = self::fromHex($transaction['status']);

        if (!empty($transaction['logs'])) {
            $transaction['transfers'] = [];
            foreach ($transaction['logs'] as $log) {
                if (!isset($log['topics']) || $log['topics'][0] != '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef') {
                    continue;
                }
                $from = mb_substr($log['topics'][1], 26);
                $to = mb_substr($log['topics'][2], 26);
                $value = self::fromHex($log['data']);

                $transaction['transfers'][$to] = compact('from', 'to', 'value');
            }
        }

        return $transaction;
    }

    public static function toHex($value, int $decimal = 0, bool $prefix = true)
    {
        if (is_numeric($value)) {
            $bn = self::toBn($value * pow(10, $decimal));
            $hex = preg_replace('/^0+(?!$)/', '', $bn->toHex(true));
        } else if (is_string($value)) {
            if (self::isHex($value)) {
                $hex = self::stripZero($value);
            } else {
                $hex = implode('', unpack('H*', $value));
            }
        } else {
            throw new InvalidArgumentException('The value to toHex function is not support.');
        }

        return ($prefix ? '0x' : '') . $hex;
    }

    public static function toEther($value)
    {
        return self::toHex($value, 18);
    }

    public static function fromEther($value)
    {
        return self::fromHex($value, 18);
    }

    /**
     * stripZero
     *
     * @param string $value
     * @return string
     */
    public static function stripZero(string $value)
    {
        $value = Str::lower($value);

        if (self::isZeroPrefixed($value)) {
            return Str::replaceFirst('0x', '', $value);
        }

        return $value;
    }


    /**
     * isHex
     *
     * @param string $value
     * @return bool
     */
    public static function isHex(string $value)
    {
        return preg_match('/^(0x)?[a-f0-9]*$/', Str::lower($value)) === 1;
    }

    /**
     * sha3
     * keccak256
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public static function sha3(string $value)
    {

        if (self::isZeroPrefixed($value)) {
            $value = pack('H*', self::stripZero($value));
        }

        $hash = Keccak::hash($value, 256);

        if ($hash === 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470') {
            return null;
        }
        return '0x' . $hash;
    }


    /**
     * toBn
     * Change number or number string to bignumber.
     *
     * @param BigInteger|string|int $number
     */
    public static function toBn($number)
    {
        if (empty($number)) {
            return new BigInteger(0);
        }

        if ($number instanceof BigInteger) {
            return $number;
        }


        if (is_numeric($number)) {

            if (ceil($number) != $number) {
                throw new InvalidArgumentException('toBn number must be a valid number.');
            }

            return new BigInteger(number_format($number, 0, '.', ''));
        }

        if (self::isHex($number)) {
            return new BigInteger($number, 16);
        }

        throw new InvalidArgumentException('toBn number must be BigInteger, string or int.');
    }
}
