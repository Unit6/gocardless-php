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
 * GoCardless request functions
 *
 * @package GoCardless\Request
 */
class Request
{
    /**
    * Configure a GET request
    *
    * @param string $url The URL to make the request to
    * @param array $params The parameters to use for the POST body
    *
    * @return string The response text
    */
    public static function get($url, array $params = array())
    {
        return self::call('GET', $url, $params);
    }

    /**
    * Configure a POST request
    *
    * @param string $url The URL to make the request to
    * @param array $params The parameters to use for the POST body
    *
    * @return string The response text
    */
    public static function post($url, array $params = array())
    {
        return self::call('POST', $url, $params);
    }

    /**
    * Configure a PUT request
    *
    * @param string $url The URL to make the request to
    * @param array $params The parameters to use for the PUT body
    *
    * @return string The response text
    */
    public static function put($url, array $params = array())
    {
        return self::call('PUT', $url, $params);
    }

    /**
    * Makes an HTTP request
    *
    * @param string $method The method to use for the request
    * @param string $url The API url to make the request to
    * @param array $params The parameters to use for the request
    *
    * @return string The response text
    */
    public static function call($method, $url, array $params = array())
    {
        $method = strtoupper( $method );

        $ch = curl_init();

        $options = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CAINFO         => dirname(__FILE__) . '/cert-bundle.crt',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => Client::USER_AGENT,
            CURLOPT_HTTPHEADER     => array(
                // response is expected as JSON.
                'Accept: application/json'
            ),
        );

        // set a custom user-agent tag to the header.
        if (isset($params['ua_tag'])) {
            $options[CURLOPT_USERAGENT] .= ' ' . $params['ua_tag'];
            unset($params['ua_tag']);
        }

        // HTTP Authentication (for confirming new payments)
        if (isset($params['http_authorization'])) {
            $options[CURLOPT_USERPWD] = $params['http_authorization'];
            unset($params['http_authorization']);
        } else {
            if ( ! isset($params['http_bearer'])) {
                throw new Exception\ClientException('Access token missing');
            }

            // set the authorization header.
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' .
                $params['http_bearer'];
            unset($params['http_bearer']);
        }

        $queryParams = ( ! empty($params)
                ? http_build_query($params, null, '&')
                : false);

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($queryParams) {
                $options[CURLOPT_POSTFIELDS] = $queryParams;
            }
        } elseif ($method === 'GET') {
            $options[CURLOPT_HTTPGET] = true;
            if ($queryParams) {
                $url .= '?' . $queryParams;
            }
        } elseif ($method == 'PUT') {
            $options[CURLOPT_PUT] = true;
            // Receiving the following curl error?:
            //    "cannot represent a stream of type MEMORY as a STDIO FILE*"
            // Try changing the first parameter of fopen() to `php://temp`
            $fh = fopen('php://memory', 'rw+');

            $options[CURLOPT_INFILE] = $fh;
            $options[CURLOPT_INFILESIZE] = 0;
        }

        // assgin the url for the request.
        $options[CURLOPT_URL] = $url;

        // set curl options
        curl_setopt_array($ch, $options);

        // send the request
        $json = curl_exec($ch);
        $error = curl_errno($ch);

        // take response code and throw an exception if failed.
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = json_decode($json, $assoc = true);

        if ($httpResponseCode < 200 || $httpResponseCode > 300) {
            $message = print_r($response, $return = true);
            throw new Exception\ResponseException($message, $httpResponseCode, $json);
        }

        curl_close($ch);

        // close the $fh handle used by PUT
        if (isset($fh)) {
            fclose($fh);
        }

        return $response;
    }
}