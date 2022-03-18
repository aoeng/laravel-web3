<?php

namespace Aoeng\Laravel\Web3;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use kornrunner\Keccak;
use phpseclib3\Crypt\EC;
use Web3p\EthereumTx\Transaction;

class Web3
{

    protected $app;

    protected $config;

    protected $address;

    protected $privateKey;


    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        $this->app = $app;

        $this->connect();
    }

    public function connect($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->config = $this->getConfig($name);

        return $this;
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['web3.default'];
    }

    protected function getConfig($name)
    {
        return $this->app['config']["web3.networks.{$name}"];
    }


    public function at($address, $privateKey = null)
    {
        $this->address = Str::lower($address);
        $this->privateKey = $privateKey;

        return $this;
    }

    public function syncing()
    {
        $response = $this->requestRPC('eth_syncing');

        if ($response instanceof \Exception) throw $response;

        if ($response == false) {
            return false;
        }

        $result = json_decode($response, true);

        foreach ($result as &$item) {
            $item = Utils::fromHex($item);
        }

        return $response;
    }

    public function getBlockByNumber($number, $full = true)
    {
        $response = $this->requestRPC('eth_getBlockByNumber', [Utils::toHex($number), $full]);

        if ($response instanceof \Exception) throw $response;

        return $response ?? [];
    }

    public function getTransactionByHash($hash)
    {
        $response = $this->requestRPC('eth_getTransactionByHash', [$hash]);

        if ($response instanceof \Exception) throw $response;

        return Utils::decodeTransaction($response);
    }

    public function getTransactionReceipt($hash)
    {
        $response = $this->requestRPC('eth_getTransactionReceipt', [$hash]);

        if ($response instanceof \Exception) throw $response;

        return Utils::decodeTransaction($response);
    }


    public function getTransactionCount($tag = 'latest')
    {
        $response = $this->requestRPC('eth_getTransactionCount', [$this->address, $tag]);

        if ($response instanceof \Exception) throw $response;

        return $response;
    }

    public function sign($data)
    {
        if (empty($this->privateKey)) {
            throw new \Exception("please unlock this address");
        }

        $transaction = new Transaction($data);

        return $transaction->sign($this->privateKey);
    }


    public function requestRPC($method, $params = [])
    {
        try {
            info('RPC:' . $method, [$this->config['rpc_host'], $params]);
            $response = Http::asJson()->post($this->config['rpc_host'], [
                "jsonrpc" => "2.0",
                "id"      => 1,
                "method"  => $method,
                "params"  => $params
            ])->throw()->json();

            info('$response', $response);
            if (isset($response['error'])) {
                throw new \Exception($response['error']['message'], $response['error']['code']);
            }

            $this->response = $response['result'];

            return $response['result'];
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function requestAPI($command, $query = [])
    {
        try {
            $response = Http::get($this->config['http_host'], array_merge([
                'module' => Str::before($command, '_'),
                'action' => Str::after($command, '_'),
                'apikey' => $this->config['key']
            ], $query))->throw()->json();


            if ($response['status'] !== "1") {
                throw new \Exception('Server error:' . $response['result'], $response['status']);
            }

            return $response['result'];
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}
