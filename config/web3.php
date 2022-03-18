<?php

return [
    'default' => env('WEB3_DEFAULT_NETWORK', 'bsc'),

    'block' => 'latest',

    'networks' => [
        'eth'      => [
            'chain_id'  => 1,
            'rpc_host'  => 'https://api.etherscan.io/',
            'http_host' => 'https://api.etherscan.io/',
            'key'       => '',
            'gas_limit' => 100000,
            'gas_price' => 5
        ],
        'bsc'      => [
            'chain_id'  => 56,
            'http_host' => 'https://api.bscscan.com/api/',
            'rpc_host'  => 'https://bsc-dataseed.binance.org/',
            'key'       => 'X4UD41CRXRUNCGSIGQIW42WAB8ZEPI46YJ',
            'gas_limit' => 100000,
            'gas_price' => 5
        ],
        'bsc-test' => [
            'chain_id'  => 97,
            'http_host' => 'https://api-testnet.bscscan.com/api/',
            'rpc_host'  => 'https://data-seed-prebsc-1-s1.binance.org:8545/',
            'key'       => 'X4UD41CRXRUNCGSIGQIW42WAB8ZEPI46YJ',
            'gas_limit' => 100000,
            'gas_price' => 5
        ],
    ],


];
