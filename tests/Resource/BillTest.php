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
 * Tests for Bill resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class BillTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $billId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->billId = BILL_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->billId);
    }

    public function testFindBill()
    {
        $bill = $this->client->getBill($this->billId);

        $data = $bill->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('amount_minus_fees', $data);
        $this->assertArrayHasKey('gocardless_fees', $data);
        $this->assertArrayHasKey('partner_fees', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('merchant_id', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('paid_at', $data);
        $this->assertArrayHasKey('source_type', $data);
        $this->assertArrayHasKey('source_id', $data);
        $this->assertArrayHasKey('uri', $data);
        $this->assertArrayHasKey('can_be_retried', $data);
        $this->assertArrayHasKey('can_be_cancelled', $data);
        $this->assertArrayHasKey('payout_id', $data);
        $this->assertArrayHasKey('is_setup_fee', $data);
        $this->assertArrayHasKey('charge_customer_at', $data);

        $this->assertEquals($this->billId, $data['id']);

        return $bill;
    }

    /**
     * @depends testFindBill
     */
    public function testRetryBill(Resource\Bill $bill)
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        if ( ! $bill->canBeRetried()) {
            $this->markTestSkipped(
              'This bill cannot be retried.'
            );
        }

        $retried = $bill->retry();

        $this->assertInstanceOf(get_class($bill), $retried);

        $status = $retried->getStatus();

        $this->assertEquals(Resource\Bill::STATUS_PENDING, $status);
    }

    /**
     * @depends testFindBill
     */
    public function testCancelBill(Resource\Bill $bill)
    {
        if ( ! $bill->canBeCancelled()) {
            $this->markTestSkipped(
              'This bill cannot be cancelled.'
            );
        }

        $cancelled = $bill->cancel();

        $this->assertInstanceOf(get_class($bill), $cancelled);

        $status = $cancelled->getStatus();

        $this->assertEquals(Resource\Bill::STATUS_CANCELLED, $status);
    }

    /**
     * @depends testFindBill
     */
    public function testRefundBill(Resource\Bill $bill)
    {
        if ( ! $bill->canBeRefunded()) {
            $this->markTestSkipped(
              'This bill cannot be refunded.'
            );
        }

        $refunded = $bill->refund();

        $this->assertInstanceOf(get_class($bill), $refunded);

        $status = $refunded->getStatus();

        $this->assertEquals(Resource\Bill::STATUS_REFUNDED, $status);
    }
}