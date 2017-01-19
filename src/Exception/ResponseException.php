<?php
/*
 * This file is part of the GoCardless package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\GoCardless\Exception;

use Unit6\GoCardless;

/**
 * Exceptions pertaining to the GoCardless API
 *
 * @author Unit6 <team@unit6websites.com>
 */
class ResponseException extends GoCardless\Exception
{
    private $json;

    /**
     * Throw a default exception
     *
     * @param string  $message Description of the error
     * @param integer $code    The returned error code
     * @param string  $json    JSON string.
     */
    public function __construct($message = 'Unknown argument error', $code = 0, $json = null)
    {
        if (empty($message)) {
            $message = 'Unknown error';
        }

        $this->json = $json;

        parent::__construct($message, $code);
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getResponse()
    {
        return json_decode($this->getJson(), $assoc = true);
    }

    public function getDescription()
    {
        $response = $this->getResponse();

        return (isset($response['description']) ? $response['description'] : '');
    }
}