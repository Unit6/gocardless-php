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
 * GoCardless resource functions
 *
 * @package GoCardless\Resource
 */
abstract class Resource
{
    protected $client;
    protected $data = array();

    /**
     * Instantiate a new instance of the resource object
     *
     * @param GoCardless\Client $client The client to use for the resource object
     * @param array             $data  The properties of the resource
     */
    function __construct(Client $client, array $data = array())
    {
        $this->client = $client;
        $this->data  = $data;
    }

    /**
     * This magic method is used to call subresources
     *
     * @param string $method The name of the method being called
     * @param array $arguments The arguments to pass to the method
     *
     * @return array The subresource index
     */
    public function __call($name, array $arguments = array())
    {
        $pattern = '/(set|get)([A-Z]{1}[\S]+)/';
        $found = preg_match($pattern, $name, $matches);

        if ($found) {
            list($name, $prefix, $key) = $matches;

            $this->camelCaseToUnderscore($key);

            if ($prefix === 'set') {
                $value = (isset($arguments[0]) ? $arguments[0] : null);
                $this->data[$key] = $value;
                return;
            } elseif ($prefix === 'get') {
                // first look for a matching attribute before attempting the
                // the list of associated resources (sub_resource_uris).
                if (isset($this->data[$key])) {
                    return $this->data[$key];
                } elseif (isset($this->data['sub_resource_uris'])
                       && isset($this->data['sub_resource_uris'][$key])) {
                    $params = (isset($arguments[0]) ? $arguments[0] : array());
                    return $this->getSubResource($key, $params);
                }
            }
        }

        throw new Exception\ResourceException('Undefined method "' . $name . '"" in Resource class.');
    }

    /**
     * Get the Client for resource requests.
     *
     * @return GoCardless\Client The client object.
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the resource data.
     *
     * @return array The properties.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Fetch an object's subresource from the API
     *
     * @param string $type The subresource to fetch
     * @param array $params The params for the subresource query
     *
     * @return object The subresource object
     */
    public function getSubResource($type, $params = array())
    {
        $sub_resource_uris = $this->data['sub_resource_uris'];

        $resource_uri = $sub_resource_uris[$type];

        // Generate subresource endpoint by snipping out the
        // right part of the sub_resource_uri
        $pattern = '/api\/v[0-9]+\//';
        $uriPath = parse_url($resource_uri, PHP_URL_PATH);
        $uri = preg_replace($pattern, '', $uriPath);

        // Extract params from subresource uri if available and create
        // subResourceParams array
        $uriQuery = parse_url($resource_uri, PHP_URL_QUERY);

        if ( ! empty($uriQuery)) {
            $subResourceParams = array();
            parse_str($uriQuery, $subResourceParams);

            //if ($paramStr = $uriQuery) {
            //    $splitParams = explode('&', $paramStr);
            //    foreach ($splitParams as $i) {
            //        list($k, $v) = explode('=', $i);
            //        $subResourceParams[$k] = $v;
            //    }
            //}

            // Overwrite params from subresource uri with passed params, if found
            $params = array_merge($params, $subResourceParams);
        }

        $resource = Client::getResourceName($type);

        $collection = array();

        $response = $this->client->request('get', $uri, $params );

        foreach ( $response as $value ) {
            $collection[] = new $resource($this->client, $value);
        }

        return $collection;
    }

    /**
     * Convert camelCase string to under_score.
     *
     * @return string
     */
    public function camelCaseToUnderscore(&$str)
    {
        // fix the camelCasing.
        $str = lcfirst($str);

        // convert camelCaseName to underscore_name.
        $str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);

        // convert entirely to lowercase underscore str.
        $str = strtolower( $str );

        return $str;
    }
}