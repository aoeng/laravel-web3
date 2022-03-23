<?php

namespace Aoeng\Laravel\Web3\Facades;

use Aoeng\Laravel\Tronscan\Tronscan;
use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static \Aoeng\Laravel\Web3\Contract connect($name = null)
 * @method static \Aoeng\Laravel\Web3\Contract at(string $address, $privateKey = null)
 * @method static \Aoeng\Laravel\Web3\Contract to(string $contractAddress, string $abi = null)
 * @method static array getAbi()
 */
class Contract extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'eth-contract';
    }

}
