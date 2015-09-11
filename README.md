stamplia-php
============

A PHP library to access the [Stamplia](https://stamplia.com) API. It requires PHP 5.4+.


Setup
-----

Using [composer](https://getcomposer.org) you can add the following to your composer.json file:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Stamplia/stamplia-php.git"
        }
    ],
    "require": {
        "php": ">=5.4.0",
        "Stamplia/stamplia-php": "~1.0"
    },
    "minimum-stability": "stable",
    "config": {
        "process-timeout": 3600
    }

Then in your PHP code include the composer autoloader, something similar to this:

    <?php
    require_once 'vendor/autoload.php';

Usage
-----

To instantiate the client it's pretty straightforward:

    $client = new \Stamplia\ApiClient();

Here's how to list templates available on our marketplace:

    $templates = $client->getTemplates();

All method parameters need to be set within a single array as the function parameter.

To log in and then be able to access private methods (user methods):
    
    $client->login(array(
        'email'         => '**',
        'password'      => '**',
        'app_id'        => '**',
        'app_secret'    => '**'
    ));

To see all methods available with this ApiClient you can use the following tool from the command line:

    php doc.php | more


Please read [our API documentation](http://doc-beta.stamplia.com/documentation/getting_started/) for a more precise description of the parameters 
that each method accepts and their response.
