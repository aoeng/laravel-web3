<?php

/**
 * This file is part of web3.php package.
 *
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Aoeng\Laravel\Web3\Contracts;

interface SolidityTypeInterface
{
    /**
     * isDynamicType
     *
     * @return bool
     */
    public function isDynamicType();

    /**
     * inputFormat
     *
     * @return string
     */
    public function inputFormat();

    public function outputFormat();
}
