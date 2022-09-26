<?php

namespace Aoeng\Laravel\Web3\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static \Aoeng\Laravel\Web3\Eth connect($name = null)
 * @method static \Aoeng\Laravel\Web3\Eth at(string $address, $privateKey = null)
 * @method static array createAccount()
 * @method static array syncing()
 * @method static array getEthPrice()
 * @method static array  getGasPrice()
 * @method static integer getGasLimit()
 *
 */
class Eth extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'eth';
    }

}
