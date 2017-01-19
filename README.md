# unit6/gocardless 

![Deprecation Notice](http://imgur.com/Eh4bAFP.jpg "Deprecated")

A PHP client for interacting with the GoCardless API.

> GoCardless is the quickest and easiest way to take one-off and
recurring payments online.
-- [GoCardless](https://gocardless.com/)

## Prerequisites

- Sign up for a [merchant account](https://gocardless.com/merchants/new) or create a [sandbox account](https://manage-sandbox.gocardless.com/signup)
- Enable **developer mode** within your dashboard.
- You'll need the `app_id`, `app_secret`, `access_token` and the `merchant_id`.
- Ensure the URI settings in your Sandbox match your environment settings.

## Environment Variables

Write your developer credentials to an environmental file, for example:

```bash
echo "export GOCARDLESS_APP_ID='ABCD1234'" > gocardless.env
echo "export GOCARDLESS_APP_SECRET='EFGH5678'" >> gocardless.env
echo "export GOCARDLESS_ACCESS_TOKEN='IJKL9012'" >> gocardless.env
echo "export GOCARDLESS_MERCHANT_ID='123457'" >> gocardless.env


```

Update the development environment 
	
	source ./gocardless.env

## System Requirements

- [PHP](http://www.php.net/) > 5.3.x
- PHP [cURL](http://www.php.net/curl) Extension
- PHP [JSON](http://www.php.net/json) Extension

This client library was tested with PHP 5.6.2 on Mac OS X.

## Documentation

This RESTful API developer documentation can be found here:
[https://developer.gocardless.com/](https://developer.gocardless.com/)


## TODO

- Add [unit6/http](https://github.com/unit6/http-php) instead of custom cURL implementation.
- Write tests.
- Publish to [Packagist](https://packagist.org/) so you can install using [Composer](https://getcomposer.org/).


## Acknowledgements

Inspired by these libraries:

- [gocardless/gocardless-php](https://github.com/gocardless/gocardless-php)

License
------------

MIT, see LICENSE.