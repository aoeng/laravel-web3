<?php


namespace Aoeng\Laravel\Web3;

use Elliptic\EC;
use Illuminate\Support\Str;
use kornrunner\Keccak;

class Eth extends Web3
{

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);
    }


    public function createAccount()
    {
        $ec = new EC('secp256k1');
        $kp = $ec->genKeyPair();
        $privateKey = $kp->getPrivate('hex');
        $publicKey = $kp->getPublic('hex');
        $address = '0x' . substr(Keccak::hash(substr(hex2bin($publicKey), 1), 256), 24);

        return compact('address', 'publicKey', 'privateKey');
    }

    public function accounts()
    {
        $response = $this->requestRPC('eth_accounts');

        if ($response instanceof \Exception) throw $response;

        return $response;
    }


    public function getBalance($contract = null, $decimal = 18)
    {

        if ($contract) {
            $response = $this->requestAPI('account_tokenbalance', [
                'contractaddress' => $contract,
                'address'         => $this->address,
                'tag'             => 'latest'
            ]);

            if ($response instanceof \Exception) throw $response;

            return Utils::fromHex($response, $decimal);
        }

        $response = $this->requestRPC('eth_getBalance', [
            $this->address,
            'latest'
        ]);

        if ($response instanceof \Exception) throw $response;

        return Utils::fromHex($response, $decimal);
    }

    public function transaction($from, $to, $value, $gas = null, $gasPrice = null, $nonce = null)
    {
        if ($value <= 0) throw new \Exception("Value: {$value} ERROR!", 331);

        $params = [
            'nonce'    => $nonce ? Utils::toHex($nonce) : $this->getTransactionCount(),
            'gas'      => Utils::toHex($gas ?? $this->config['gas_limit']),
            'gasPrice' => Utils::toHex($gasPrice ?? $this->config['gas_price'], 9),
            'from'     => Str::lower($from),
            'to'       => Str::lower($to),
            'value'    => Utils::toEther($value),
            'chainId'  => $this->config['chain_id']
        ];

        $signData = Utils::toHex($this->sign($params));

        $response = $this->requestRPC('eth_sendRawTransaction', [$signData]);

        if ($response instanceof \Exception) throw $response;

        return $response;
    }

}
