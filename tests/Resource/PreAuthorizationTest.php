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
 * Tests for Pre-Authorization resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class PreAuthorizationTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $preAuthorizationId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->preAuthorizationId = PRE_AUTHORIZATION_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->preAuthorizationId);
    }

    public function testFindPreAuthorization()
    {
        $preAuthorization = $this->client->getPreAuthorization($this->preAuthorizationId);

        $data = $preAuthorization->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('max_amount', $data);
        $this->assertArrayHasKey('setup_fee', $data);
        $this->assertArrayHasKey('remaining_amount', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertArrayHasKey('interval_length', $data);
        $this->assertArrayHasKey('interval_unit', $data);
        $this->assertArrayHasKey('next_interval_start', $data);
        $this->assertArrayHasKey('merchant_id', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('uri', $data);
        $this->assertArrayHasKey('sub_resource_uris', $data);
        $this->assertArrayHasKey('expires_at', $data);
        $this->assertArrayHasKey('created_at', $data);

        $this->assertEquals($this->preAuthorizationId, $data['id']);

        return $preAuthorization;
    }

    /**
     * @depends testFindPreAuthorization
     */
    public function testCreateBillFromPreAuthorization(Resource\PreAuthorization $preAuthorization)
    {
        $attr = array(
            'amount'  => '5.00'
        );

        $bill = $preAuthorization->createBill($attr);

        $expectedType = Client::getResourceName('bills');

        $this->assertInstanceOf($expectedType, $bill);

        return $preAuthorization;
    }

    /**
     * @depends testCreateBillFromPreAuthorization
     */
    public function testCancelPreAuthorization(Resource\PreAuthorization $preAuthorization)
    {
        $cancelled = $preAuthorization->cancel();

        $this->assertInstanceOf(get_class($preAuthorization), $cancelled);

        $status = $cancelled->getStatus();

        $this->assertEquals(Resource\PreAuthorization::STATUS_CANCELLED, $status);
    }
}