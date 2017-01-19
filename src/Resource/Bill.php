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
 * GoCardless bill functions
 *
 * @package GoCardless\Bill
 */
class Bill extends GoCardless\Resource
{
    /**
     * Waiting for the money to clear from the customer's account.
     */
    const STATUS_PENDING = 'pending';

    /**
     * Bill has been successfully been debited from the
     * customer's account. It is being held by GoCardless
     * pending a withdrawal to the merchant.
     */
    const STATUS_PAID = 'paid';

    /**
     * Bill could not be debited from a customer's account.
     * This usually means that there were insufficient funds
     * in the customer's account.
     */
    const STATUS_FAILED = 'failed';

    /**
     * Bill has been refunded by you or by GoCardless at your request.
     */
    const STATUS_REFUNDED = 'refunded';

    /**
     * Bill has been reversed by the customer at their bank
     * under the Direct Debit Guarantee.
     */
    const STATUS_CHARGEDBACK = 'chargedback';

    /**
     * Bill was cancelled by the merchant or customer before
     * it was submitted to the banks.
     */
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The bill has been paid out to the merchant. Takes up
     * to one business day to reach the merchant's bank account.
     * You can find more details by using the bill's payout_id
     * with the payouts resource.
     */
    const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * The API endpoint for bills
     *
     * @var string $endpoint
     */
    public static $endpoint = '/bills';

    /**
     * Instantiate a new instance of the resource
     *
     * @param GoCardless\Client $client The client to use for the resource
     * @param array             $data  The properties of the resource
     */
    public function __construct(Client $client, array $data = array())
    {
        parent::__construct($client, $data);
    }

    /**
     * Alias for checking whether a bill can be retried.
     *
     * If a bill fails, its status will change to failed.
     * At this point it can be retried if the can_be_retried
     * attribute is true.
     *
     * @return bool
     */
    public function canBeRetried()
    {
        return $this->getCanBeRetried();
    }

    /**
     * Alias for checking whether a bill can be cancelled.
     *
     * A pending bill can be cancelled if it has not yet
     * been submitted to the banking system, which is the
     * case if the can_be_cancelled attribute is true.
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return $this->getCanBeCancelled();
    }

    /**
     * Alias for checking whether a bill can be refunded.
     *
     * A bill can be refunded if its can_be_refunded attribute
     * is true, and it has a status of paid or withdrawn.
     *
     * Note: This operation is irreversible.
     *
     * This endpoint will only be available if refunds have
     * been enabled on your merchant account. You can contact
     * help@gocardless.com to request that we enable this for you.
     *
     * @return bool
     */
    public function canBeRefunded()
    {
        // Note: can_be_refunded may not exist in the response.
        //       it may need to be enabled by GoCardless.
        return (isset($this->data['can_be_refunded']) && $this->getCanBeRefunded());
    }

    /**
     * Attempt to collect a bill with status 'failed' again
     *
     * @return GoCardless\Resource\Bill The result of the retry query
     */
    public function retry()
    {
        $endpoint = self::$endpoint . '/' . $this->id . '/retry';

        return new self($this->client, $this->client->request('post', $endpoint));
    }

    /**
    * Fetch the payout for a bill, if a payout_id is recorded
    *
    * @return GoCardless\Resource\Payout A payout object representing the payout
    */
    public function payout()
    {
        $id = $this->getPayoutId();

        if (is_null($id)) {
            throw new Exception\ClientException('Cannot fetch payout for a bill that has not been paid out');
        }

        return Payout::find($id);
    }

    /**
     * Cancel a bill in the API
     *
     * @return GoCardless\Resource\Bill The result of the cancel query
     */
    public function cancel()
    {
        $uri = self::$endpoint . '/' . $this->getId() . '/cancel';

        $response = $this->client->request('put', $uri);

        return new self($this->client, $response);
    }

    /**
     * Refund a bill
     *
     * @return GoCardless\Resource\Bill The result of the refund query
     */
    public function refund()
    {
        $uri = self::$endpoint . '/' . $this->getId() . '/refund';

        $response = $this->client->request('post', $uri);

        return new self($this->client, $response);
    }

   /**
    * Fetch a bill item from the API
    *
    * @param string      $id     The id of the bill to fetch
    * @param object|null $client The client object to use to make the query
    *
    * @return GoCardless\Resource\Bill The bill object
    */
    public static function find($id, $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $endpoint = self::$endpoint . '/' . $id;

        return new self($client, $client->request('get', $endpoint));
    }
}
