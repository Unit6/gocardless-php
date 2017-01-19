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
 * GoCardless merchant functions
 *
 * @package GoCardless\Merchant
 */
class Merchant extends GoCardless\Resource
{
    /**
     * The API endpoint for merchants
     *
     * @var string $endpoint
     */
    public static $endpoint = '/merchants';

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
     * Fetch a merchant object from the API
     *
     * @param string      $id     The id of the merchant to fetch
     * @param object|null $client The client object to use to make the query
     *
     * @return GoCardless\Resource\Merchant The merchant object
     */
    public static function find($id, $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $response = $client->request('get', self::$endpoint . '/' . $id);

        return new self($client, $response);
    }
}