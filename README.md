<p align="center">
    <h1 align="center">Tinkoff Open Api SDK by Tsvetkov</h1>
</p>



[![Latest Stable Version](https://img.shields.io/packagist/v/tsvetkov/tinkoff_open_api.svg)](https://packagist.org/packages/tsvetkov/tinkoff_open_api)
[![Total Downloads](https://img.shields.io/packagist/dt/tsvetkov/tinkoff_open_api.svg)](https://packagist.org/packages/tsvetkov/tinkoff_open_api)

Installation
------------
In order to install extension use Composer. Either run

```
php composer.phar require tsvetkov/tinkoff_open_api
```

or add

```
"tsvetkov/tinkoff_open_api": "*"
```

to the require section of your composer.json.

Basic Usage
-----------

Initialization

```
use tsvetkov\tinkoff_open_api\Client;

$client = new Client($token);

$stocks = $client->marketStocks();
```