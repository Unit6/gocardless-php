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
 * GoCardless pre-authorisation functions
 *
 * @package GoCardless\Merchant
 */
class PreAuthorization extends GoCardless\Resource
{
    /**
     * A pre-authorization that has not been confirmed at
     * the end of the Connect flow. It will be deleted from
     * the database after a few hours.
     */
    const STATUS_INACTIVE = 'inactive';

    /**
     * A valid pre-authorization. The merchant can now create
     * bills under this pre-authorization.
     */
    const STATUS_ACTIVE = 'active';

    /**
     * Pre-authorization been terminated. No more bills can
     * be created.
     */
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Pre-authorization reached its expiration date. No more
     * bills can be created.
     */
    const STATUS_EXPIRED = 'expired';

    /**
    * The API endpoint for pre-authorizations
    *
    * @var string $endpoint
    */
    public static $endpoint = '/pre_authorizations';

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
     * Fetch a pre-authorisation object from the API
     *
     * @param string      $id     The id of the pre-authorisation to fetch
     * @param object|null $client The client object to use to make the query
     *
     * @return GoCardless\Resource\PreAuthorization The pre-authorisation object
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
     * Create a bill under an existing pre-authorization
     *
     * @param array $attrs The properties of the bill being created
     *
     * @return object The pre-authorisations object
     */
    public function createBill($attrs)
    {
        if ( ! isset($attrs['amount'])) {
            throw new Exception\ArgumentsException('amount required');
        }

        $pre_authorization_id = $this->getId();

        if (is_null($pre_authorization_id) &&
              isset($attrs['pre_authorization_id'])) {
            $pre_authorization_id = $attrs['pre_authorization_id'];
        }

        if (is_null($pre_authorization_id)) {
            throw new Exception\ArgumentsException('pre_authorization_id required');
        }

        $params = array(
            'bill' => array(
                'pre_authorization_id' => $pre_authorization_id,
                'amount'               => $attrs['amount']
            )
        );

        if (isset($attrs['name'])) {
            $params['bill']['name'] = $attrs['name'];
        }

        if (isset($attrs['description'])) {
            $params['bill']['description'] = $attrs['description'];
        }

        if (isset($attrs['charge_customer_at'])) {
            $params['bill']['charge_customer_at'] = $attrs['charge_customer_at'];
        }

        $uri = Bill::$endpoint;

        $response = $this->client->request('post', $uri, $params);

        return new Bill($this->client, $response);
    }

    /**
     * Cancel a pre-authorisation
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