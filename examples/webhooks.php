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
 * This is a demo of the webhook functionality of GoCardless.
 * https://developer.gocardless.com/#webhook-overview
 *
 * You can use this script with the webhook testing tool in the developer tab.
 * At the moment, the best way to learn about the different webhooks is to
 * change the options in the webhook tester and read the annotations that pop
 * up.
 *
 * Start the built-in PHP webserver
 * $ php -S 127.0.0.1:8000 -t examples/
 *
 * Start ngrok
 * $ ngrok 8000
 *
 * Post JSON to endpoint:
 * curl -H "Content-Type: application/json" -X POST -d '{"foo":"bar"}' http://localhost:8000/webhooks.php
 */

$client = new GoCardless\Client($config);

// fetch the raw body of the HTTP request.
$input = file_get_contents('php://input');
$webhook = json_decode($input, $assoc = true);

$jsonErrorCode = json_last_error();
$method = $_SERVER['REQUEST_METHOD'];
$isValid = false;

if ($jsonErrorCode === JSON_ERROR_NONE && isset($webhook['payload'])) {
    $isValid = $client->isValidWebhook($webhook['payload']);
}

// attempt to open or create the file for inspecting payloads.
$log = fopen('webhooks.log', 'a');

fwrite($log, 'Date: ' . date('c') . PHP_EOL);
fwrite($log, 'Method: ' . $method . PHP_EOL);
fwrite($log, 'inputLength: ' . strlen($input) . PHP_EOL);
fwrite($log, 'jsonErrorCode: ' . $jsonErrorCode . PHP_EOL);
fwrite($log, 'isValid: ' . ($isValid ? 'Yes' : 'No') . PHP_EOL);
fwrite($log, print_r($webhook, $return = true) . PHP_EOL . PHP_EOL);
fclose($log);

header('HTTP/1.1 ' . ($isValid ? '200 OK' : '403 Invalid signature'));