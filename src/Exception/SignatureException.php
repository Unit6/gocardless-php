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
 * Exceptions pertaining to the signature
 *
 * @author Unit6 <team@unit6websites.com>
 */
class SignatureException extends GoCardless\Exception
{
    /**
     * Throw a default exception
     *
     * @param string  $message Description of the error
     * @param integer $code    The returned error code
     */
    public function __construct($message = 'Signature error', $code = 0)
    {
        parent::__construct($message, $code);
    }
}