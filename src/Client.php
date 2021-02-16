<?php

namespace tsvetkov\tinkoff_open_api;

use DateTime;
use GuzzleHttp\Exception\RequestException;
use Throwable;
use tsvetkov\tinkoff_open_api\Enum\BrokerAccountType;
use tsvetkov\tinkoff_open_api\Enum\CandleInterval;
use tsvetkov\tinkoff_open_api\Enum\DateFormat;
use tsvetkov\tinkoff_open_api\Enum\OperationType;
use tsvetkov\tinkoff_open_api\Exception\BaseException;
use tsvetkov\tinkoff_open_api\Exception\WrongEnvException;

/**
 * Class Client
 * @package tsvetkov\tinkoff_open_api
 */
class Client
{
    private string $token;
    private string $baseUrl;
    private bool $isSandBox;

    /**
     * Client constructor.
     * @param string $token
     * @param bool $isSandBox
     */
    public function __construct(string $token, bool $isSandBox = true)
    {
        $this->token = $token;
        $this->isSandBox = $isSandBox;
        if ($this->isSandBox) {
            $this->baseUrl = 'https://api-invest.tinkoff.ru/openapi/sandbox/';
        } else {
            $this->baseUrl = 'https://api-invest.tinkoff.ru/openapi/';
        }
    }

    /**
     * @param string $accountType
     * @see BrokerAccountType
     *
     * @return array
     *
     * @throws WrongEnvException
     */
    public function sandboxRegister(string $accountType = BrokerAccountType::TINKOFF): array
    {
        if (!$this->isSandBox) {
            throw new WrongEnvException();
        }

        return $this->post('sandbox/register', ['brokerAccountType' => $accountType]);
    }

    /**
     * @param string $currency
     * @param mixed $balance
     * @param string|null $brokerAccountId
     *
     * @return array
     *
     * @throws WrongEnvException
     */
    public function sandboxCurrenciesBalance(string $currency, $balance, ?string $brokerAccountId = null): array
    {
        if (!$this->isSandBox) {
            throw new WrongEnvException();
        }

        $url = 'sandbox/currencies/balance';
        if ($brokerAccountId) {
            $url .= '?brokerAccountId=' . urlencode($brokerAccountId);
        }

        return $this->post($url, [
            'currency' => $currency,
            'balance' => $balance,
        ]);
    }

    /**
     * @param string $figi
     * @param mixed $balance
     * @param string|null $brokerAccountId
     *
     * @return array
     *
     * @throws WrongEnvException
     */
    public function sandboxPositionsBalance(string $figi, $balance, ?string $brokerAccountId = null): array
    {
        if (!$this->isSandBox) {
            throw new WrongEnvException();
        }

        $url = 'sandbox/positions/balance';
        if ($brokerAccountId) {
            $url .= '?brokerAccountId=' . urlencode($brokerAccountId);
        }

        return $this->post($url, [
            'figi' => $figi,
            'balance' => $balance,
        ]);
    }

    /**
     * @param string $brokerAccountId
     *
     * @return array
     *
     * @throws WrongEnvException
     */
    public function sandboxRemove(string $brokerAccountId): array
    {
        if (!$this->isSandBox) {
            throw new WrongEnvException();
        }

        return $this->post('sandbox/remove?brokerAccountId=' . $brokerAccountId);
    }

    /**
     * @param string $brokerAccountId
     *
     * @return array
     *
     * @throws WrongEnvException
     */
    public function sandboxClear(string $brokerAccountId): array
    {
        if (!$this->isSandBox) {
            throw new WrongEnvException();
        }

        return $this->post('sandbox/clear?brokerAccountId=' . $brokerAccountId);
    }

    /**
     * @return array
     */
    public function marketStocks(): array
    {
        return $this->get('market/stocks');
    }

    /**
     * @return array
     */
    public function marketBonds(): array
    {
        return $this->get('market/bonds');
    }

    /**
     * @return array
     */
    public function marketEtfs(): array
    {
        return $this->get('market/etfs');
    }

    /**
     * @return array
     */
    public function marketCurrencies(): array
    {
        return $this->get('market/currencies');
    }

    /**
     * @param string $figi
     * @param int $depth
     *
     * @return array
     */
    public function marketOrderBook(string $figi, int $depth): array
    {
        return $this->get('market/orderbook', [
            'figi' => $figi,
            'depth' => $depth,
        ]);
    }

    /**
     * @param string $figi
     * @param DateTime $from
     * @param DateTime $to
     * @param string $interval
     * @see CandleInterval
     *
     * @return array
     */
    public function marketCandles(string $figi, DateTime $from, DateTime $to, string $interval): array
    {
        return $this->get('market/candles', [
            'figi' => $figi,
            'from' => $from->format(DateFormat::FORMAT),
            'to' => $to->format(DateFormat::FORMAT),
            'interval' => $interval,
        ]);
    }

    /**
     * @param string $figi
     *
     * @return array
     */
    public function marketSearchByFigi(string $figi): array
    {
        return $this->get('market/search/by-figi', [
            'figi' => $figi,
        ]);
    }

    /**
     * @param string $ticker
     *
     * @return array
     */
    public function marketSearchByTicker(string $ticker): array
    {
        return $this->get('market/search/by-ticker', [
            'ticker' => $ticker,
        ]);
    }

    /**
     * @return array
     */
    public function userAccounts(): array
    {
        return $this->get('user/accounts');
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param string|null $figi
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function operations(
        DateTime $from,
        DateTime $to,
        ?string $figi = null,
        ?string $brokerAccountId = null
    ): array {
        $params = [
            'from' => $from->format(DateFormat::FORMAT),
            'to' => $to->format(DateFormat::FORMAT),
        ];
        if ($figi) {
            $params['figi'] = $figi;
        }
        if ($brokerAccountId) {
            $params['brokerAccountId'] = $brokerAccountId;
        }
        return $this->get('operations', $params);
    }

    /**
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function portfolio(?string $brokerAccountId = null): array
    {
        $params = [];
        if ($brokerAccountId) {
            $params['brokerAccountId'] = $brokerAccountId;
        }

        return $this->get('portfolio', $params);
    }

    /**
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function portfolioCurrencies(?string $brokerAccountId = null): array
    {
        $params = [];
        if ($brokerAccountId) {
            $params['brokerAccountId'] = $brokerAccountId;
        }

        return $this->get('portfolio/currencies', $params);
    }

    /**
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function orders(?string $brokerAccountId = null): array
    {
        $params = [];
        if ($brokerAccountId) {
            $params['brokerAccountId'] = $brokerAccountId;
        }
        return $this->get('orders', $params);
    }

    /**
     * @param string $figi
     * @param int $lots
     * @param string $operation
     * @see OperationType
     * @param mixed $price
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function ordersLimitOrder(string $figi, int $lots, string $operation, $price, ?string $brokerAccountId = null): array
    {
        $url = 'orders/limit-order?figi=' . urlencode($figi);
        if ($brokerAccountId) {
            $url .= '&brokerAccountId=' . urlencode($brokerAccountId);
        }

        return $this->post($url, [
            'lots' => $lots,
            'operation' => $operation,
            'price' => $price,
        ]);
    }

    /**
     * @param string $figi
     * @param int $lots
     * @param string $operation
     * @see OperationType
     * @param string|null $brokerAccountId
     *
     * @return array
     */
    public function ordersMarketOrder(string $figi, int $lots, string $operation, ?string $brokerAccountId = null): array
    {
        $url = 'orders/market-order?figi=' . urlencode($figi);
        if ($brokerAccountId) {
            $url .= '&brokerAccountId=' . urlencode($brokerAccountId);
        }

        return $this->post($url, [
            'lots' => $lots,
            'operation' => $operation,
        ]);
    }


    /**
     * @param string $orderId
     * @param string|null $brokerAccountId
     *
     * @return array
     * @see OperationType
     */
    public function ordersCancel(string $orderId, ?string $brokerAccountId = null): array
    {
        $url = 'orders/cancel?orderId=' . urlencode($orderId);
        if ($brokerAccountId) {
            $url .= '&brokerAccountId=' . urlencode($brokerAccountId);
        }

        return $this->post($url);
    }

    /**
     * @param string $url
     * @param array $params
     *
     * @return array
     */
    protected function get(string $url, array $params = []): array
    {
        $url = $this->baseUrl . $url;
        try {
            $client = new \GuzzleHttp\Client();
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ];
            if (!empty($params)) {
                $url  .= '?';
                $queryParams = [];
                foreach ($params as $name => $value) {
                    $queryParams[] = "$name=" . urlencode($value);
                }
                $url .=  implode('&', $queryParams);
            }
            return json_decode($client->get($url, $options)->getBody()->getContents(), true);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                return json_decode($exception->getResponse()->getBody()->getContents(), true);
            }
            // TODO
        } catch (Throwable $throwable) {
            // TODO?
        }
        return [];
    }

    /**
     * @param string $url
     * @param array $params
     *
     * @return array
     */
    protected function post(string $url, array $params = []): array
    {
        $url = $this->baseUrl . $url;
        try {
            $client = new \GuzzleHttp\Client();
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ];
            if (!empty($params)) {
                $options['body'] = json_encode($params);
            }
            return json_decode($client->post($url, $options)->getBody()->getContents(), true);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                return json_decode($exception->getResponse()->getBody()->getContents(), true);
            }
            // TODO
        } catch (Throwable $throwable) {
            // TODO?
        }
        return [];
    }
}
