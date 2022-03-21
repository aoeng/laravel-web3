## 以太类型的链的Web3

### Install

```bash 
composer require aoeng/laravel-web3

php artisan vendor:publish --tag=web3
```


### Used
```php

Web3::getTransactionByHash('0x54c5595bbf06ca67e45a36eb19119f7fe783539594d3c882e2e1904bb416d390')

Eth::at('0x7efB8b4d6eEC79529308a496D4A2260079ad3093')->getBalance('0xa8B42dD6efE5967659c87bFBaD5A30cC1f6fD8E5')

Contract::to($contractAddress)->at($address1, $privateKey1)->send('transfer', ['0x72f3Fe55D9A05edfD380477F16c4c997d6fEAc1F', 11])

// .... 其他的自行摸索 

```

### 还不是很完善 欢迎Pull requests
