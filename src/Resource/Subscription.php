<?php
/*
 * This file is part of the GoCardless package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\GoCardless\Resource;

use Unit6\GoCardless;
use Unit6\GoCardless\Client;
use Unit6\GoCardless\Exception;

/**
 * GoCardless subscription functions
 *
 * @package GoCardless\Merchant
 */
class Subscription extends GoCardless\Resource
{
    /**
     * A subscription that has not been confirmed
     * at the end of the Connect flow. It will be
     * deleted from the database after a few hours.
     */
    const STATUS_INACTIVE = 'inactive';

    /**
     * A subscription that has not yet expired or
     * been cancelled. Bills will automatically be
     * created by GoCardless according to the interval (e.g. monthly).
     */
    const STATUS_ACTIVE = 'active';

    /**
     * A subscription that has been terminated by the
     * customer or merchant. No more bills will be created.
     */
    const STATUS_CANCELLED = 'cancelled';

    /**
     * A subscription that has reached its expiration date.
     * No more bills will be created.
     */
    const STATUS_EXPIRED = 'expired';

    /**
     * The API endpoint for subscriptions
     *
     * @var string $endpoint
     */
    public static $endpoint = '/subscriptions';

    /**
     * Instantiate a new instance of the resource
     *
     * @param GoCardless\Client $client The client to use for the resource
     * @param array             $data  The properties of the resource
     */
    function __construct(Client $client, array $data = array())
    {
        parent::__construct($client, $data);
    }

    /**
     * Fetch a subscription object from the API
     *
     * @param string      $id     The id of the subscription to fetch
     * @param object|null $client The client object to use to make the query
     *
     * @return GoCardless\Resource\Subscription The subscription object
     */
    public static function find($id, $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $response = $client->request('get', self::$endpoint . '/' . $id);

        return new self($client, $response);
    }

    /**
     * Cancel a subscription
     *
     * @return object The result of the cancel query
     */
    public function cancel()
    {
        $uri = self::$endpoint . '/' . $this->getId() . '/cancel';

        $response = $this->client->request('put', $uri);

        return new self($this->client, $response);
    }
}