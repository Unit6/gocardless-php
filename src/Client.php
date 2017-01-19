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
 * GoCardless client functions
 *
 * @package GoCardless\Client
 */
class Client
{
    /**
     * The current user agent of the client.
     *
     * @var constant USER_AGENT
     */
    const USER_AGENT = 'unit6-gocardless/v0.1.0';

    /**
     * The current Client instance.
     *
     * @var GoCardless\Client $instance
     */
    protected static $instance;

    /**
     * Account details for this instance of GoCardless\Client
     *
     * @var array $config
     */
    public $config;

    /**
     * The environment: 'sandbox' (default) or 'production'
     *
     * @var string $environment
     */
    public $environment = 'sandbox';

    /**
     * The endpoint for the API to use for all client requests
     *
     * @var string $endpoint
     */
    public $endpoint;

    /**
     * Array of possible endpoints to use
     *
     * @var array $endpoints
     */
    public $endpoints = array(
        'production' => 'https://gocardless.com',
        'sandbox'    => 'https://sandbox.gocardless.com'
    );

    /**
     * The API version to use.
     *
     * @var string $version
     */
    public $version = '1';

    /**
     * The url to redirect the user to
     *
     * @var string $redirectUri
     */
    public $redirectUri;

    /**
     * List of valid country codes in ISO 3166-1 (alpha-2) format
     *
     * NOTE: With the exception of the United Kingdom, all other
     *       countries are valid for EUR payments only.
     *
     * @var array $countries
     */
    public static $countries = array(
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'CY' => 'Cyprus',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'LV' => 'Latvia',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'MC' => 'Monaco',
        'NL' => 'Netherlands',
        'PT' => 'Portugal',
        'SM' => 'San Marino',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'GB' => 'United Kingdom'
    );

    /**
     * List of valid currencies.
     *
     * @var array $currencies
     */
    public static $currencies = array(
        'GBP' => 'British Pound Sterling', // (default)
        'EUR' => 'Euro'
    );

    /**
     * Constructor, creates a new instance of GoCardless\Client
     *
     * @param array $config Parameters
     */
    public function __construct(array $config)
    {
        if ( ! isset($config['app_id'])) {
            throw new Exception\ClientException('No app_id specified');
        }

        if ( ! isset($config['app_secret'])) {
            throw new Exception\ClientException('No app_secret specfied');
        }

        // set the API version to use.
        if (isset($config['version'])) {
            $this->version = $config['version'];
        }

        // If environment is not set then default to sandbox
        if (isset($config['environment'])) {
            $this->environment = $config['environment'];
        }

        if (isset($config['base_url'])) {
            $this->endpoint = $config['base_url'];
            unset($config['base_url']);
        } else {
            // Otherwise set it based on environment
            $this->endpoint = $this->endpoints[$this->environment];
        }

        $this->config = $config;

        self::setInstance($this);
    }

    /**
     * Set the Client instance.
     *
     * @param GoCardless\Client
     */
    public static function setInstance(Client $client)
    {
        self::$instance = $client;
    }

    /**
     * Get the Client instance.
     *
     * @return GoCardless\Client
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Get the client application ID
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->config['app_id'];
    }

    /**
     * Get the client application Secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->config['app_secret'];
    }

    /**
     * Get the application merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->config['merchant_id'];
    }

    /**
     * Get the HTTP Basic Authorization header for API requests.
     *
     * @return string
     */
    public function getAuthorizationHeader()
    {
        return $this->getClientId() . ':' . $this->getClientSecret();
    }

    /**
     * Generate the OAuth authorize URL
     *
     * @param array $options The parameters to use
     *
     * @return string The generated URL
     */
    public function getAuthorizeUrl(array $options)
    {
        if ( ! isset($options['redirect_uri'])) {
            throw new Exception\ArgumentsException('redirect_uri required');
        }

        $required = array(
            'client_id' => $this->getClientId(),
            'scope' => 'manage_merchant',
            'response_type' => 'code'
        );

        $params = array_merge($required, $options);

        $request = self::generateQueryStr($params);

        return $this->endpoint . '/oauth/authorize/?' . $request;
    }

    /**
     * Fetch an access token for the current user
     *
     * @param array $params The parameters to use
     *
     * @return array Array containing the Merchant ID ('merchant_id') and Access Token ('access_token')
     */
    public function getAccessToken(array $params)
    {
        if ( ! isset($params['redirect_uri'])) {
            throw new Exception\ArgumentsException('redirect_uri required');
        }

        $params['http_authorization'] = $this->getAuthorizationHeader();

        $response = $this->request('post', '/oauth/access_token', $params);

        $merchant = explode(':', $response['scope']);
        $merchant_id = isset($merchant[1]) ? $merchant[1] : null;
        $access_token = $response['access_token'];

        return array(
            'merchant_id'   => $merchant_id,
            'access_token'  => $access_token
        );
    }

    /**
     * Generate a URL to give a user to create a new bill
     *
     * @param array $params Parameters to use to generate the URL
     *
     * @return string The generated URL
     */
    public function getBillUrl(array $params)
    {
        return $this->getPaymentUrl('bill', $params);
    }

    /**
     * Generate a URL to give a user to create a new subscription
     *
     * @param array $params Parameters to use to generate the URL
     *
     * @return string The generated URL
     */
    public function getSubscriptionUrl(array $params)
    {
        return $this->getPaymentUrl('subscription', $params);
    }

    /**
     * Generate a URL to give a user to create a new pre-authorized payment
     *
     * @param array $params Parameters to use to generate the URL
     *
     * @return string The generated URL
     */
    public function getPreAuthorizationUrl(array $params)
    {
        return $this->getPaymentUrl('pre_authorization', $params);
    }

    /**
     * Returns the merchant associated with the client's access token
     *
     * @param string $id The id of the merchant to fetch
     *
     * @return object The merchant object
     */
    public function getMerchant($id = null)
    {
        if (is_null($id)) {
            $id = $this->config['merchant_id'];
        }

        return Resource\Merchant::find($id);
    }

    /**
     * Get a specific bill
     *
     * @param string $id The id of the bill to fetch
     *
     * @return object The bill object matching the id requested
     */
    public function getBill($id)
    {
        return Resource\Bill::find($id);
    }

    /**
     * Get a specific subscription
     *
     * @param string $id The id of the subscription to fetch
     *
     * @return object The subscription matching the id requested
     */
    public function getSubscription($id)
    {
        return Resource\Subscription::find($id);
    }

    /**
     * Get a specific pre_authorization
     *
     * @param string $id The id of the pre_authorization to fetch
     *
     * @return object The pre-authorization matching the id requested
     */
    public function getPreAuthorization($id)
    {
        return Resource\PreAuthorization::find($id);
    }

    /**
     * Get a specific payout
     *
     * @param string $id The id of the payout to fetch
     *
     * @return object The payout object matching the id requested
     */
    public function getPayout($id)
    {
        return Resource\Payout::find($id);
    }

    /**
     * Get a specific user
     *
     * @param string $id The id of the user to fetch
     *
     * @return object The user object matching the id requested
     */
    public function getUser($id)
    {
        return Resource\User::find($id);
    }

    /**
     * Create a new bill under a given pre-authorization
     *
     * @param array $params Must include pre_authorization_id and amount
     *
     * @return string The new bill object
     */
    public function createBill(array $params)
    {
        $pre_auth = new Resource\PreAuthorization($this);

        return $pre_auth->create_bill($params);
    }

    /**
    * Send an HTTP request to confirm the creation of a new payment resource
    *
    * @param array $params Parameters to send with the request
    *
    * @return Bill/Subscription/PreAuthorization The confirmed resource
    */
    public function getResourceConfirmation(array $params)
    {
        // First validate signature
        // Then send confirm request

        // List of required params
        $required = array(
            'resource_id',
            'resource_type'
        );

        $data = array();

        // Loop through required params
        // Add to $data or throw exception if missing
        foreach ($required as $key => $value) {
            if ( ! isset($params[$value])) {
                throw new Exception\ArgumentsException($value . ' missing');
            }

            $data[$value] = $params[$value];
        }

        // state is optional
        if (isset($params['state'])) {
            $data['state'] = $params['state'];
        }

        // resource_uri is optional
        if (isset($params['resource_uri'])) {
            $data['resource_uri'] = $params['resource_uri'];
        }

        $signatureValidationData = array(
            'data'      => $data,
            'secret'    => $this->getClientSecret(),
            'signature' => $params['signature']
        );

        if ( ! $this->isValidSignature($signatureValidationData)) {
            throw new Exception\SignatureException();
        }

        // Sig valid, now send confirm request
        $confirm = array(
            'resource_id'   => $params['resource_id'],
            'resource_type' => $params['resource_type']
        );

        // Use HTTP Basic Authorization
        $confirm['http_authorization'] = $this->getAuthorizationHeader();

        // If no method-specific redirect sent, use class level if available
        if ( ! isset($params['redirect_uri'])
            && isset($this->redirectUri)) {
            $confirm['redirect_uri'] = $this->redirectUri;
        }

        $type = $params['resource_type'];
        $id = $params['resource_id'];

        // Execute query
        $response = $this->request('post', '/confirm', $confirm);

        if (isset($response['success'])
               && $response['success'] === true) {
            $resource = Client::getResourceName($type);

            // append plural to the resource.
            $uri = '/' . $type . 's/' . $id;

            $response = $this->request('get', $uri);

            return new $resource($this, $response);
        } else {
            throw new Exception\ClientException('Failed to fetch the confirmed resource.');
        }
    }

    /**
     * Generate a new payment url
     *
     * @param string $type Payment type
     * @param string $params The specific parameters for this payment
     *
     * @return string The new payment URL
     */
    public function getPaymentUrl($type, array $params)
    {
        $mandatory = array(
            'client_id' => $this->getClientId(),
            'nonce'     => self::generateNonce(),
            'timestamp' => self::getCurrentTime()
        );

        // $params are passed in
        // Optional $params are saved in $request and removed from $params
        // $params now only contains params for the payments
        // $payment_params is created containing sub-object named after $type
        // Merge $payment_params, $request and $mandatory params
        // Sign
        // Generate query string
        // Return resulting url

        // validate the currency.
        if (isset($params['currency'])) {
            $currency = $params['currency'];
            if ( ! isset(self::$currencies[$currency])) {
                throw new Exception\CurrencyException('invalid currency: ' . $currency);
            }
        }

        // validate a country code, if provided.
        if (   isset($params['user'])
            && isset($params['user']['country_code'])) {
            $country_code = $params['user']['country_code'];
            if ( ! isset(self::$countries[$country_code])) {
                throw new Exception\CountryException('invalid country code: ' . $country_code);
            }
        }

        // Declare empty array
        $request = array();

        // Add in merchant id
        $params['merchant_id'] = $this->getMerchantId();

        // Define optional parameters
        $options = array(
            'redirect_uri',
            'cancel_uri',
            'state'
        );

        // Loop through optional parameters
        foreach ($options as $i) {
            if (isset($params[$i])) {
                $request[$i] = $params[$i];
                unset($params[$i]);
            }
        }

        // If no method-specific redirect submitted then
        // use class level if available
        if ( ! isset($request['redirect_uri'])
            && isset($this->redirectUri)) {
            $request['redirect_uri'] = $this->redirectUri;
        }

        // Create array of payment params
        $payment = array();
        $payment[$type] = $params;

        // Put together all the bits: passed params including
        // payment params & mandatory params
        $request = array_merge($request, $payment, $mandatory);

        // Generate signature
        $request['signature'] = self::generateSignature($request, $this->getClientSecret());


        // Generate query string from all parameters
        $queryStr = self::generateQueryStr($request);

        // Generate url NB. Pluralises resource
        $url = $this->endpoint . '/connect/' . $type . 's/new?' . $queryStr;

        return $url;
    }

    /**
     * Make a request to the API
     *
     * @param string $method The request method to use
     * @param string $uri    The API endpoint to call
     * @param string $params The parameters to send with the request
     *
     * @return object The returned object
     */
    public function request($method, $uri, array $params = array())
    {
        // If there is no http_authorization, try checking for access_token
        if ( ! isset($params['http_authorization'])) {
            // No http_authorization and no access_token? Fail
            if ( ! isset($this->config['access_token'])) {
                throw new Exception\ClientException('Access token missing');
            }

            // access_token found so set Authorization header to contain bearer
            $params['http_bearer'] = $this->config['access_token'];
        }

        // Set application specific user agent suffix if found
        if (isset($this->config['ua_tag'])) {
            $params['ua_tag'] = $this->config['ua_tag'];
        }

        if (substr($uri, 0, 6) == '/oauth') {
            // OAuth API calls don't require /api/v1 base
            $url = $this->endpoint . $uri;
        } else {
            // http://sandbox.gocardless.com | /api/v1 | /test
            $url = $this->endpoint . '/api/v' . $this->version . $uri;
        }

        return Request::call($method, $url, $params);
    }

    /**
     * Test whether a webhook is valid or not
     *
     * @param array params The contents of the webhook in array form
     *
     * @return boolean If valid returns true
     */
    public function isValidWebhook(array $params)
    {
        $sig = $params['signature'];
        unset($params['signature']);

        if ( ! isset($sig)) {
            return false;
        }

        $data = array(
            'data'      => $params,
            'secret'    => $this->getClientSecret(),
            'signature' => $sig
        );

        return $this->isValidSignature($data);
    }

    /**
     * Confirm whether a signature is valid
     *
     * @param array $params Should include data, secret and signature
     *
     * @return boolean True or false
     */
    public function isValidSignature(array $params)
    {
        $signature = self::generateSignature($params['data'], $params['secret']);

        return ($signature === $params['signature']);
    }

    /**
     * Get the class name with associated namespace prefix.
     *
     * @return string Class name.
     */
    public static function getResourceName($type)
    {
        return __NAMESPACE__ . '\\Resource\\'
            . self::camelize(self::singularize($type));
    }

    /**
     * Get current combined date and time in UTC.
     *
     * @return string date time in ISO 8601 format
     */
    public static function getCurrentTime()
    {
        // Create new UTC date object
        $tz = new \DateTimeZone('UTC');
        $dt = new \DateTime($time = null, $tz);

        return $dt->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Generates a nonce
     *
     * @return string Base64 encoded nonce
     */
    public static function generateNonce()
    {
        $i = 1;
        $str = '';

        do {
            $str .= rand(1, 256);
            $i++;
        } while ($i <= 45);

        return base64_encode($str);
    }

    /**
     * Generate a signature for a request given the app secret
     *
     * @param array $params The parameters to generate a signature for
     * @param string $key The key to generate the signature with
     *
     * @return string A URL-encoded string of parameters
     */
    public static function generateSignature($params, $key)
    {
        $queryStr = self::generateQueryStr($params);

        return hash_hmac('sha256', $queryStr, $key);
    }

    /**
     * Generates, encodes, re-orders variables for the query string.
     *
     * @param array $params The specific parameters for this payment
     * @param array $pairs Pairs
     * @param string $namespace The namespace
     *
     * @return string An encoded string of parameters
     */
    public static function generateQueryStr($params, &$pairs = array(), $namespace = null)
    {
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if (is_int($k)) {
                    self::generateQueryStr($v, $pairs, $namespace . '[]');
                } else {
                    self::generateQueryStr($v, $pairs,
                    $namespace !== null ? $namespace . "[$k]" : $k);
                }
            }

            if ( ! is_null($namespace)) {
                return $pairs;
            }

            if (empty($pairs)) {
                return '';
            }

            usort($pairs, array(__CLASS__, 'sortPairs'));

            $strs = array();
            foreach ($pairs as $pair) {
                $strs[] = $pair[0] . '=' . $pair[1];
            }

            return implode('&', $strs);
        } else {
            $pairs[] = array(rawurlencode($namespace), rawurlencode($params));
        }
    }

    /**
    * Sorts a pair
    *
    * @param array $a
    * @param array $b
    *
    * @return int
    */
    public static function sortPairs($a, $b)
    {
        $keys = strcmp($a[0], $b[0]);

        if ($keys !== 0) {
            return $keys;
        }

        return strcmp($a[1], $b[1]);
    }

    /**
    * Strip underscores and convert to CamelCaps
    *
    * @param string $str The string to process
    *
    * @return string The result
    */
    public static function camelize($str)
    {
        return implode(array_map('ucfirst', explode('_', $str)));
    }

    /**
    * Convert a word to the singular
    *
    * @param string $str The string to process
    *
    * @return string The result
    */
    public static function singularize($str)
    {
        if (substr($str, -1) == 's') {
            return substr($str, 0, -1);
        } elseif (substr($str, -1) == 'i') {
            return substr($str, 0, -1) . 'us';
        } else {
            return $str;
        }
    }
}