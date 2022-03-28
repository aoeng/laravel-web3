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

use Aoeng\Laravel\Web3\Types\AddressType;
use Aoeng\Laravel\Web3\Types\BoolType;
use Aoeng\Laravel\Web3\Types\BytesType;
use Aoeng\Laravel\Web3\Types\IntegerType;
use Aoeng\Laravel\Web3\Types\StringType;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Contract extends Web3
{

    protected $abi = [];

    /**
     * constructor
     *
     * @var array
     */
    protected $constructor = [];

    /**
     * functions
     *
     * @var array
     */
    protected $functions = [];

    /**
     * events
     *
     * @var array
     */
    protected $events = [];

    /**
     * toAddress
     *
     * @var string
     */
    protected $contractAddress;


    protected $function;

    protected $typeClassMap = [
        'int'     => IntegerType::class,
        'uint'    => IntegerType::class,
        'string'  => StringType::class,
        'address' => AddressType::class,
        'bool'    => BoolType::class,
        'bytes'   => BytesType::class
    ];

    /**
     * bytecode
     *
     * @var string
     */
    protected $bytecode;

    /**
     * defaultBlock
     *
     * @var mixed
     */
    protected $defaultBlock;

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        $this->block();

    }

    public function block($block = null)
    {
        $this->defaultBlock = $block ?? config('web3.block');
    }

    /**
     * getFunctions
     *
     * @return array
     */
    public function functions()
    {
        return $this->functions;
    }

    /**
     * getEvents
     *
     * @return array
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * getConstructor
     *
     * @return array
     */
    public function constructor()
    {
        return $this->constructor;
    }

    public function appendTypes(array $types)
    {
        $this->typeClassMap = array_merge($this->typeClassMap, $types);
    }

    /**
     * bytecode
     *
     * @param string $bytecode
     * @return $this
     */
    public function bytecode($bytecode)
    {
        $this->bytecode = Utils::stripZero($bytecode);

        return $this;
    }

    /**
     * abi
     *
     * @param string $contractAddress
     * @param string|null $abi
     * @return $this
     * @throws \Exception
     */
    public function to(string $contractAddress, string $abi = null)
    {

        $this->contractAddress = Str::lower($contractAddress);

        if ($abi) {
            $this->abi = json_decode($abi, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidArgumentException('abi decode error: ' . json_last_error_msg());
            }
        } else {
            $this->getAbi();
        }

        foreach ($this->abi as $item) {
            if (isset($item['type'])) {
                if ($item['type'] === 'function') {
                    $this->functions[$item['name']] = $item;
                } elseif ($item['type'] === 'constructor') {
                    $this->constructor = $item;
                } elseif ($item['type'] === 'event') {
                    $this->events[$item['name']] = $item;
                }
            }
        }

        return $this;
    }

    public function getAbi()
    {
        if (!empty($this->abi)) {
            return $this->abi;
        }

        $response = $this->requestAPI('contract_getabi', ['address' => $this->contractAddress]);

        if ($response instanceof \Exception) throw $response;

        $this->abi = json_decode($response, true);

        return $this->abi;
    }

    /**
     * Call function method.
     *
     * @param string $function
     * @param $params
     * @param $from
     * @param $gas
     * @param $gasPrice
     * @param $value
     * @return array
     * @throws \Exception
     */
    public function call(string $function, $params, $from = null, $gas = null, $gasPrice = null, $value = null)
    {
        if (!isset($this->functions[$function])) {
            throw new InvalidArgumentException('function not found: ' . $function);
        }

        $this->function = $function;

        $data = $this->encode($params);
        $functionSignature = $this->encodeFunctionSignature($function);

        $transaction = array_filter(compact('from', 'gas', 'gasPrice', 'value'));

        $transaction['to'] = $this->contractAddress;
        $transaction['data'] = $functionSignature . Utils::stripZero($data);

        $response = $this->requestRPC('eth_call', [$transaction, $this->defaultBlock]);

        if ($response instanceof \Exception) throw $response;

        return $this->decode($response);
    }

    /**
     * Send function method.
     *
     * @param $function
     * @param $params
     * @param $nonce
     * @param $gas
     * @param $gasPrice
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function send($function, $params, $gas = null, $gasPrice = null, $nonce = null, $value = null)
    {
        if (!isset($this->functions[$function])) {
            throw new InvalidArgumentException('function not found: ' . $function);
        }

        $this->function = $function;

        $data = $this->encode($params);

        $functionSignature = $this->encodeFunctionSignature($function);

        $transaction = [
            'nonce'    => $nonce ? Utils::toHex($nonce) : $this->getTransactionCount(),
            'gas'      => Utils::toHex($gas ?? $this->config['gas_limit']),
            'gasPrice' => Utils::toHex($gasPrice ?? $this->config['gas_price'], 9),
            'from'     => $this->address,
            'to'       => $this->contractAddress,
            'chainId'  => $this->config['chain_id']
        ];

        $value && $transaction['value'] = Utils::toEther($value);

        $transaction['to'] = $this->contractAddress;
        $transaction['data'] = $functionSignature . Utils::stripZero($data);

        $signData = Utils::toHex($this->sign($transaction));

        $response = $this->requestRPC('eth_sendRawTransaction', [$signData]);

        if ($response instanceof \Exception) throw $response;

        return $response;
    }

    public function encodeFunctionSignature($functionName)
    {
        return mb_substr(Utils::sha3($functionName . '(' . implode(',', array_column($this->functions[$functionName]['inputs'], 'type')) . ')'), 0, 10);
    }

    public function encodeEventSignature($functionName)
    {
        return Utils::sha3($functionName);
    }

    public function encode($params)
    {

        if (!isset($this->functions[$this->function]['inputs'])) {
            return '';
        }

        if (count($this->functions[$this->function]['inputs']) !== count($params)) {
            throw new InvalidArgumentException('encodeParameters number of types must equal to number of params.');
        }


        $length = 0;
        $data = [];
        $encodeData = [];

        foreach ($this->functions[$this->function]['inputs'] as $key => $input) {
            $className = $this->getTypeClassName($input['type']);

            if ((!empty($input['name']) && !isset($params[$input['name']])) && !isset($params[$key])) {
                throw new InvalidArgumentException('encodeParameters are missing.');
            }

            $value = !empty($input['name']) && isset($params[$input['name']]) ? $params[$input['name']] : $params[$key];

            $data[$key] = $row = (new $className($value, $input['type']))->encode();


            if (!$row['dynamic']) {
                $encodeData[$key] = $row['value'];
                $length += $row['length'];
            } else {
                $encodeData[$key] = 0;
                $length += 32;
            }
        }

        //动态数据
        foreach ($data as $key => $item) {
            if ($item['dynamic']) {
                $encodeData[$key] = Utils::integerFormat($length);

                $encodeData[] = $item['value'];
                $length += $item['length'];
            }
        }

        return '0x' . implode('', $encodeData);
    }

    public function decode(string $data)
    {
        $decodeArr = [];

        if (empty($this->functions[$this->function]['outputs'])) {
            return [];
        }

        $data = Utils::stripZero($data);

        $offset = 0;

        foreach ($this->functions[$this->function]['outputs'] as $key => $output) {
            $className = $this->getTypeClassName($output['type']);
            $length = $this->getTypeLength($output['type']);

            $value = Str::substr($data, $offset, $length);

            $offset += $length;

            $decodeArr[empty($output['name']) ? $key : $output['name']] = (new $className($value))->decode();
        }

        return $decodeArr;
    }

    public function getTypeLength($typeName)
    {
        if (!Str::contains($typeName, '[')) {
            return 64;
        }

        // TODO::多维数组长度

        return 64;
    }


    public function getTypeClassName($typeName)
    {
        foreach ($this->typeClassMap as $key => $class) {
            if (Str::startsWith($typeName, $key)) {
                return $class;
            }
        }

        throw new InvalidArgumentException('type not found');
    }
}
