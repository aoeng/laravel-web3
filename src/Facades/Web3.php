<?php

namespace Aoeng\Laravel\Web3\Facades;

use Aoeng\Laravel\Tronscan\Tronscan;
use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static \Aoeng\Laravel\Web3\Web3 connect($name = null)
 * @method static \Aoeng\Laravel\Web3\Web3 at($address, $privateKey = null)
 * @method static string requestRPC($method, $params = [], $id = null)
 * @method static string requestAPI($command, $query = [])
 * @method static array|false syncing()
 * @method static int blockNumber()
 * @method static array getBlockByNumber($number, $full = true)
 * @method static array getTransactionByHash($hash)
 * @method static array getTransactionReceipt($hash)
 */
class Web3 extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'web3';
    }

}
