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
 * Tests for User resource.
 *
 * @author Unit6 <team@unit6websites.com>
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    protected $client;
    protected $userId;

    protected function setUp()
    {
        global $config;

        $this->client = new Client($config);
        $this->userId = USER_ID;
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->userId);
    }

    public function testFindUser()
    {
        $user = $this->client->getUser($this->userId);

        $data = $user->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayHasKey('last_name', $data);
        $this->assertArrayHasKey('company_name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('created_at', $data);

        $this->assertEquals($this->userId, $data['id']);

        return $user;
    }
}