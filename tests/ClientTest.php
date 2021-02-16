<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocSignatureInspection */

use PHPUnit\Framework\TestCase;
use tsvetkov\tinkoff_open_api\Client;
use tsvetkov\tinkoff_open_api\Enum\CandleInterval;
use tsvetkov\tinkoff_open_api\Enum\OperationType;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    private static Client $client;

    public static function setUpBeforeClass(): void
    {
        $config = json_decode(file_get_contents(__DIR__ . '/fixtures-local.json'), true);
        self::$client = new Client($config['token'], true);
    }

    /**
     * @covers Client::sandboxRegister()
     */
    public function testSandboxRegister()
    {
        $response = self::$client->sandboxRegister();
        $this->assertEquals('Ok', $response['status']);
        $this->assertNotEmpty($response['payload']);
        $this->assertNotEmpty($response['payload']['brokerAccountId']);
        return $response['payload']['brokerAccountId'];
    }

    /**
     * @covers Client::sandboxCurrenciesBalance()
     * @depends testSandboxRegister
     */
    public function testSandboxCurrenciesBalance(string $brokerAccountId)
    {
        $response = self::$client->sandboxCurrenciesBalance('USD', 1000000, $brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::marketStocks()
     */
    public function testMarketStocks()
    {
        $response = self::$client->marketStocks();

        $this->assertEquals('Ok', $response['status']);
        $firstFigi = $response['payload']['instruments'][0]['figi'] ?? null;
        $this->assertNotEmpty($firstFigi);

        return $firstFigi;
    }

    /**
     * @covers Client::marketBonds()
     */
    public function testMarketBonds()
    {
        $response = self::$client->marketBonds();

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::marketEtfs()
     */
    public function testMarketEtfs()
    {
        $response = self::$client->marketEtfs();

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::marketCurrencies()
     */
    public function testMarketCurrencies()
    {
        $response = self::$client->marketCurrencies();

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::marketOrderBook()
     * @depends testMarketStocks
     */
    public function testMarketOrderBook(string $figi)
    {
        $depth = 3;
        $response = self::$client->marketOrderBook($figi, $depth);

        $this->assertEquals('Ok', $response['status']);
        $this->assertEquals($depth, $response['payload']['depth']);
    }

    /**
     * @covers Client::sandboxPositionsBalance()
     * @depends testSandboxRegister
     * @depends testMarketStocks
     */
    public function testSandboxPositionsBalance(string $brokerAccountId, string $figi)
    {
        $response = self::$client->sandboxPositionsBalance($figi, 10, $brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::marketCandles()
     * @depends testMarketStocks
     */
    public function testMarketCandles(string $figi)
    {
        $currentDay  = new DateTime();
        $dayBefore = new DateTime('-1 day');
        $response = self::$client->marketCandles($figi, $dayBefore, $currentDay, CandleInterval::FIFTEEN_MINUTES);

        $this->assertEquals('Ok', $response['status']);
        $this->assertEquals($figi, $response['payload']['figi']);
        $this->assertEquals(CandleInterval::FIFTEEN_MINUTES, $response['payload']['interval']);
    }

    /**
     * @covers Client::marketSearchByFigi()
     * @depends testMarketStocks
     */
    public function testMarketSearchByFigi(string $figi)
    {
        $response = self::$client->marketSearchByFigi($figi);

        $this->assertEquals('Ok', $response['status']);
        $this->assertEquals($figi, $response['payload']['figi']);

        return $response['payload']['ticker'];
    }

    /**
     * @covers Client::marketSearchByTicker()
     * @depends testMarketSearchByFigi
     */
    public function testMarketSearchByTicker(string $ticker)
    {
        $response = self::$client->marketSearchByTicker($ticker);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::userAccounts()
     * @depends testSandboxRegister
     */
    public function testUserAccount(string $brokerAccountId)
    {
        $response = self::$client->userAccounts();

        $this->assertEquals('Ok', $response['status']);

        $isExist = false;
        foreach ($response['payload']['accounts'] as $account) {
            if ($account['brokerAccountId'] == $brokerAccountId) {
                $isExist = true;
                break;
            }
        }

        $this->assertTrue($isExist);
    }

    /**
     * @covers Client::userAccounts()
     * @depends testSandboxRegister
     * @depends testMarketStocks
     */
    public function testOperations(string $brokerAccountId, string $figi)
    {
        $currentDay  = new DateTime();
        $dayBefore = new DateTime('-1 day');
        $response = self::$client->operations($dayBefore, $currentDay, $figi, $brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::portfolio()
     * @depends testSandboxRegister
     */
    public function testPortfolio(string $brokerAccountId)
    {
        $response = self::$client->portfolio($brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::portfolioCurrencies()
     * @depends testSandboxRegister
     * @depends testSandboxCurrenciesBalance
     */
    public function testPortfolioCurrencies(string $brokerAccountId)
    {
        $response = self::$client->portfolioCurrencies($brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::orders()
     * @depends testSandboxRegister
     */
    public function testOrders(string $brokerAccountId)
    {
        $response = self::$client->orders($brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::ordersMarketOrder()
     * @depends testSandboxRegister
     * @depends testMarketStocks
     */
    public function testOrdersMarketOrder(string $brokerAccountId, string $figi)
    {
        $response = self::$client->ordersMarketOrder($figi, 1, OperationType::BUY, $brokerAccountId);

        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::ordersLimitOrder()
     * @depends testSandboxRegister
     * @depends testMarketStocks
     */
    public function testOrdersLimitOrder(string $brokerAccountId, string $figi)
    {
        $response = self::$client->ordersLimitOrder($figi, 1, OperationType::BUY, 0.01, $brokerAccountId);

        $this->assertEquals('Ok', $response['status']);

        return $response['payload']['orderId'];
    }

    /**
     * @covers Client::ordersCancel()
     * @depends testSandboxRegister
     * @depends testOrdersLimitOrder
     */
//    public function testOrdersCancel(string $brokerAccountId, string $orderId)
//    {
//        $response = self::$client->ordersCancel($orderId, $brokerAccountId);
//
//        $this->assertEquals('Ok', $response['status']);
//      TODO find way to create pending order (now all orders filled already)
//    }

    /**
     * @covers Client::sandboxClear()
     * @depends testSandboxRegister
     */
    public function testSandboxClear(string $brokerAccountId)
    {
        $response = self::$client->sandboxClear($brokerAccountId);
        $this->assertEquals('Ok', $response['status']);
    }

    /**
     * @covers Client::sandboxRemove()
     * @depends testSandboxRegister
     */
    public function testSandboxRemove(string $brokerAccountId)
    {
        $response = self::$client->sandboxRemove($brokerAccountId);
        $this->assertEquals('Ok', $response['status']);
    }
}
