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
 * Tests for Subscription resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $subscriptionId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->subscriptionId = SUBSCRIPTION_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->subscriptionId);
    }

    public function testFindSubscription()
    {
        $subscription = $this->client->getSubscription($this->subscriptionId);

        $data = $subscription->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertArrayHasKey('interval_length', $data);
        $this->assertArrayHasKey('interval_unit', $data);
        $this->assertArrayHasKey('next_interval_start', $data);
        $this->assertArrayHasKey('setup_fee', $data);
        $this->assertArrayHasKey('merchant_id', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('uri', $data);
        $this->assertArrayHasKey('sub_resource_uris', $data);
        $this->assertArrayHasKey('start_at', $data);
        $this->assertArrayHasKey('expires_at', $data);
        $this->assertArrayHasKey('created_at', $data);

        $this->assertEquals($this->subscriptionId, $data['id']);

        return $subscription;
    }

    /**
     * @depends testFindSubscription
     */
    public function testCancelSubscription(Resource\Subscription $subscription)
    {
        $cancelled = $subscription->cancel();

        $this->assertInstanceOf(get_class($subscription), $cancelled);

        $status = $cancelled->getStatus();

        $this->assertEquals(Resource\Subscription::STATUS_CANCELLED, $status);
    }
}