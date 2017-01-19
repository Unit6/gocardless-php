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
 * Tests for Client.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $urlPattern = '|([A-Za-z]{3,9})://([-;:&=\+\$,\w]+@{1})?([-A-Za-z0-9\.]+)+:?(\d+)?((/[-\+~%/\.\w]+)?\??([-\+=&;%@\.\w]+)?#?([\w]+)?)?|';

    protected function setUp()
    {
        global $config;

        $this->config = $config;
    }

    protected function tearDown()
    {
        unset($this->config);
    }

    public function testCreateClient()
    {
        $client = new Client($this->config);

        $this->assertInstanceOf(__NAMESPACE__ . '\\Client', $client);

        return $client;
    }

    /**
     * @depends testCreateClient
     * @expectedException Unit6\GoCardless\Exception\CurrencyException
     */
    public function testExceptionForInvalidCurrencyCode(Client $client)
    {
        $payment = array(
            'name'     => 'testExceptionForInvalidCurrencyCode',
            'amount'   => '10.00',
            'currency' => 'XXX'
        );

        $url = $client->getBillUrl($payment);

        $this->assertRegExp($this->urlPattern, $url);
    }

    /**
     * @depends testCreateClient
     * @expectedException Unit6\GoCardless\Exception\CountryException
     */
    public function testExceptionForInvalidCountryCode(Client $client)
    {
        $payment = array(
            'name'     => 'testExceptionForInvalidCurrencyCode',
            'amount'   => '10.00',
            'user'   => array(
                'country_code' => 'XX'
            )
        );

        $url = $client->getBillUrl($payment);

        $this->assertRegExp($this->urlPattern, $url);
    }

    /**
     * @depends testCreateClient
     */
    public function testGetNewBillUrl(Client $client)
    {
        $payment = array(
            'name'   => 'testGetNewBillUrl',
            'amount' => '10.00'
        );

        $url = $client->getBillUrl($payment);

        $this->assertRegExp($this->urlPattern, $url);
    }

    /**
     * @depends testCreateClient
     */
    public function testGetNewSubscriptionUrl(Client $client)
    {
        $payment = array(
            'name'            => 'testGetNewSubscriptionUrl',
            'amount'          => '15.00',
            'interval_length' => 1,
            'interval_unit'   => 'month'
        );

        $url = $client->getSubscriptionUrl($payment);

        $this->assertRegExp($this->urlPattern, $url);
    }

    /**
     * @depends testCreateClient
     */
    public function testGetNewPreAuthorizationUrl(Client $client)
    {
        $payment = array(
            'name'            => 'testGetNewPreAuthorizationUrl',
            'max_amount'      => '100.00',
            'interval_length' => 1,
            'interval_unit'   => 'month',
            // send user data to pre-populate form.
            'user'            => array(
                'first_name' => 'John',
                'last_name'  => 'Smith',
                'email'      => 'john.smith@example.org'
            )
        );

        $url = $client->getPreAuthorizationUrl($payment);

        $this->assertRegExp($this->urlPattern, $url);
    }

    /**
     * @depends testCreateClient
     * @expectedException Unit6\GoCardless\Exception\ResponseException
     */
    public function testFailedResourceConfirmation(Client $client)
    {
        $queryStr = 'resource_id=0WMX7QY4E3&resource_type=pre_authorization&resource_uri=https%3A%2F%2Fsandbox.gocardless.com%2Fapi%2Fv1%2Fpre_authorizations%2F0WMX7QY4E3&signature=8f5061148adf7d7d2d619af33ec1a281cd8a0cec33502a2484c274348eaf90a3';

        parse_str($queryStr, $params);

        $this->assertArrayHasKey('resource_id', $params);
        $this->assertArrayHasKey('resource_type', $params);
        $this->assertArrayHasKey('resource_uri', $params);
        $this->assertArrayHasKey('signature', $params);

        $confirmed = $client->getResourceConfirmation($params);
    }
}