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
 * GoCardless user functions
 *
 * @package GoCardless\Merchant
 */
class User extends GoCardless\Resource
{
    /**
     * The API endpoint for users
     *
     * @var string $endpoint
     */
    public static $endpoint = '/users';

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
     * Fetch a user object from the API
     *
     * @param string      $id     The id of the user to fetch
     * @param object|null $client The client object to use to make the query
     *
     * @return GoCardless\Resource\User The user object
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