<?php
/**
 * This file is part of the GoCardless package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require '../autoload.php';
require '../tests/Bootstrap.php';

use Unit6\GoCardless;

/**
 * This is a demo of a partner integration with GoCardless.
 * https://developer.gocardless.com/#partner-guide
 *
 * Start the built-in PHP webserver
 * $ php -S 127.0.0.1:8000 -t examples/
 *
 * Start ngrok
 * $ ngrok 8000
 *
 * Load the page in a browser.
 * http://127.0.0.1:8000/partner.php
 */

$host = 'http://localhost:8000';
#$host = 'http://69f92de.ngrok.com';
$redirectUri = $host . '/partner.php';

// fail if no account details set.
if (   ( ! isset($config['app_id'])     || empty($config['app_id']))
    || ( ! isset($config['app_secret']) || empty($config['app_secret']))) {
    echo '<p>Sign up to <a href="https://gocardless.com/">GoCardless</a> '
        . ' and copy your sandbox API credentials from the "Developer"  '
        . 'tab into the top of this script.</p>';
    exit;
}

unset($config['access_token']);
unset($config['merchant_id']);

$client = new GoCardless\Client($config);

if (isset($_GET['code'])) {
    // Code being passed.

    $params = array(
        'client_id'     => $config['app_id'],
        'code'          => $_GET['code'],
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code'
    );

    // Fetching token returns merchant_id and access_token.
    try {
        $token = $client->getAccessToken($params);

        $config['access_token'] = $token['access_token'];
        $config['merchant_id'] = $token['merchant_id'];

        // create new Client object
        $client = new GoCardless\Client( $config );

        echo '<h2>Authorization Successful!</h2>'
            . '<p>Copy and paste this access token into the '
            . 'top of the code for this page to continue testing '
            . 'the partner demo. In your own app, you\'ll want to '
            . 'save it to your database.</p>';

        echo '<pre>Access Token: ' . $config['access_token'] . PHP_EOL
            . 'Merchant ID: ' . $config['merchant_id'] . '</pre>';
    } catch (GoCardless\Exception $e) {
        echo 'Access Token Error: ' . $e->getDescription();
        exit;
    }
}

if (isset($config['access_token']) && isset($config['merchant_id'])) {
    // We have an access token, run some API queries
    // using our shiny new token.

    $merchant = $client->getMerchant();

    echo '<h2>Partner Authorization</h2>';
    echo '<p>Access token found. Returns information about '
        . 'the merchant associated with the client\'s access token:<p>';
    echo '<blockquote><pre>';
    print_r($merchant);
    echo '</pre></blockquote>';

} else {
    // No access token so show new authorization link
    $params = array(
        'redirect_uri' => $redirectUri
    );

    $url = $client->getAuthorizeUrl($params);

    echo '<h2>Partner Authorization</h2>';
    echo '<p><a href="' . $url . '">Authorize Application</a></p>';
}