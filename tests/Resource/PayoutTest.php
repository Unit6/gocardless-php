<?php
/*
 * This file is part of the GoCardless package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\GoCardless;

/**
 * Tests for Payout resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class PayoutTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $payoutId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->payoutId = PAYOUT_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->payoutId);
    }

    public function testFindPayout()
    {
        $payout = $this->client->getPayout($this->payoutId);

        $data = $payout->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('bank_reference', $data);
        $this->assertArrayHasKey('paid_at', $data);
        $this->assertArrayHasKey('transaction_fees', $data);
        $this->assertArrayHasKey('app_ids', $data);
        $this->assertArrayHasKey('created_at', $data);

        $this->assertEquals($this->payoutId, $data['id']);

        return $payout;
    }
}