<?php
/*
 * This file is part of the GoCardless package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Set error reporting and display errors settings.
 * You will want to change these when in production.
 */
error_reporting(-1);
ini_set('display_errors', true);

$config = array(
    'app_id'       => getenv('GOCARDLESS_APP_ID'),
    'app_secret'   => getenv('GOCARDLESS_APP_SECRET'),
    'access_token' => getenv('GOCARDLESS_ACCESS_TOKEN'),
    'merchant_id'  => getenv('GOCARDLESS_MERCHANT_ID'),
    'environment'  => 'sandbox', // sandbox or production'
    'version'      => '1'
);

/**
 * Assign identifiers for GoCardless to be used
 * as fixtures for unit tests:
 */
define('MERCHANT_ID', '');
define('BILL_ID', '');
define('USER_ID', '');
define('SUBSCRIPTION_ID', '');
define('PRE_AUTHORIZATION_ID', '');
define('PAYOUT_ID', '');