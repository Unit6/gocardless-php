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
 * Tests for Merchant resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class MerchantTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $merchantId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->merchantId = MERCHANT_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->merchantId);
    }

    public function testFindMerchant()
    {
        $merchant = $this->client->getMerchant($this->merchantId);

        $data = $merchant->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayHasKey('last_name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('uri', $data);
        $this->assertArrayHasKey('balance', $data);
        $this->assertArrayHasKey('pending_balance', $data);
        $this->assertArrayHasKey('next_payout_date', $data);
        $this->assertArrayHasKey('next_payout_amount', $data);
        $this->assertArrayHasKey('hide_variable_amount', $data);
        $this->assertArrayHasKey('sub_resource_uris', $data);
        $this->assertArrayHasKey('gbp_balance', $data);
        $this->assertArrayHasKey('eur_balance', $data);
        $this->assertArrayHasKey('gbp_pending_balance', $data);
        $this->assertArrayHasKey('eur_pending_balance', $data);

        $this->assertEquals($this->merchantId, $data['id']);

        return $merchant;
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantBills(Resource\Merchant $merchant)
    {
        $bills = $merchant->getBills();

        $type = Client::getResourceName('bills');

        $this->assertContainsOnlyInstancesOf($type, $bills);
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantBillsWithFiltering(Resource\Merchant $merchant)
    {
        $sourceId = '0WMRP3CDVV';

        $filteredBy = array(
            'source_id' => $sourceId
        );

        $bills = $merchant->getBills($filteredBy);

        $type = Client::getResourceName('bills');

        $this->assertContainsOnlyInstancesOf($type, $bills);

        $bill = $bills[0];

        $this->assertEquals($sourceId, $bill->getSourceId());
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantBillsWithPagination(Resource\Merchant $merchant)
    {
        $perPage = 3;

        $pagination = array(
            'per_page' => $perPage,
            'page' => '1'
        );

        $bills = $merchant->getBills($pagination);

        $type = Client::getResourceName('bills');

        $this->assertContainsOnlyInstancesOf($type, $bills);

        $this->assertCount($perPage, $bills);
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantSubscriptions(Resource\Merchant $merchant)
    {
        $subscriptions = $merchant->getSubscriptions();

        $type = Client::getResourceName('subscriptions');

        $this->assertContainsOnlyInstancesOf($type, $subscriptions);
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantPreAuthorizations(Resource\Merchant $merchant)
    {
        $preAuthorizations = $merchant->getPreAuthorizations();

        $type = Client::getResourceName('preAuthorizations');

        $this->assertContainsOnlyInstancesOf($type, $preAuthorizations);
    }

    /**
     * @depends testFindMerchant
     */
    public function testGetMerchantPayouts(Resource\Merchant $merchant)
    {
        $payouts = $merchant->getPayouts();

        $type = Client::getResourceName('payouts');

        $this->assertContainsOnlyInstancesOf($type, $payouts);
    }
}
?>