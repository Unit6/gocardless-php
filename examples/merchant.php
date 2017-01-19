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
 * This is a demo of a typical merchant integration with GoCardless.
 * https://developer.gocardless.com/#generating-payment-links
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
$redirectUri = $host . '/merchant.php';

// fail if no account details set.
if (   ( ! isset($config['app_id'])     || empty($config['app_id']))
    || ( ! isset($config['app_secret']) || empty($config['app_secret']))) {
    echo '<p>Sign up to <a href="https://gocardless.com/">GoCardless</a> '
        . ' and copy your sandbox API credentials from the "Developer"  '
        . 'tab into the top of this script.</p>';
    exit;
}

$client = new GoCardless\Client($config);

try {
    $merchant = $client->getMerchant();
} catch (GoCardless\Exception $e) {
    echo 'Merchant Error: ' . $e->getDescription();
    exit;
}

echo '<h1>' . $merchant->getName() . '</h1>';

if (isset($_GET['resource_id'],  $_GET['resource_type'],
          $_GET['resource_uri'], $_GET['signature'])) {
    // if resource_id and resource_type are present
    // you'll need to try confirming the payment.
    $params = array(
        'resource_id'   => $_GET['resource_id'],
        'resource_type' => $_GET['resource_type'],
        'resource_uri'  => $_GET['resource_uri'],
        'signature'     => $_GET['signature']
    );

    // state is optional.
    if (isset($_GET['state'])) {
        $params['state'] = $_GET['state'];
    }

    try {
        $confirmed = $client->getResourceConfirmation($params);
    } catch (GoCardless\Exception $e) {
        echo 'Resource Confirmation Error: ' . $e->getDescription();
        exit;
    }

    echo '<h2>Payment Confirmed</h2>';
    echo '<pre>';
    print_r($confirmed->getData());
    echo '</pre>';

    exit;
}

echo '<h2>New Payment URLs</h2>';

$defaults = array(
    'cancel_uri'   => $redirectUri,
    'redirect_uri' => $redirectUri,
    'state'        => 'UserID.' . time()
);

// new bill.
$payment = array_merge($defaults, array(
    'amount'  => '30.00',
    'name'    => 'Donation'
));

$url = $client->getBillUrl($payment);

echo '<p><a href="' . $url . '">New bill</a></p>';


// new subscription.
$payment = array_merge($defaults, array(
    'amount'          => '10.00',
    'interval_length' => 1,
    'interval_unit'   => 'month'
));

$url = $client->getSubscriptionUrl($payment);

echo '<p><a href="' . $url . '">New subscription</a></p>';


// new pre-authorization.
$payment = array_merge($defaults, array(
    'max_amount'      => '100.00',
    'interval_length' => 1,
    'interval_unit'   => 'month',
    'user' => array(
        'first_name'  => 'Tom',
        'last_name'   => 'Blomfield',
        'email'       => 'tom@gocardless.com'
    )
));

$url = $client->getPreAuthorizationUrl($payment);

echo '<p><a href="' . $url . '">New pre-authorized payment</a></p>';
echo '<p><em>Note: The "new pre-authorization" link is also a '
    . 'demo of pre-populated user data.</em></p>';
