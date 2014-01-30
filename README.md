stamplia-php
============

A PHP library to access the stamplia API


Usage
-----

Add the following to your composer.json file

        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/Kiwup/stamplia-php.git"
            }
        ],
        "require": {
            "php": ">=5.3.3",
            "Kiwup/stamplia-php": "dev-master"
        },

Then in your PHP file, require the composer autoloader, something similar to this

    <?php
    require('vendor/autoload.php');

You can then create a OAuth provider with your app data, create a new API client, and use it:

    $provider = new Kiwup\StampliaClient\Provider\Stamplia(array(
        'clientId'  =>  '12_1fo52xvb0k1wcsck0oc88o8cos8gw44w8gksc0okgcks4gc40g',
        'clientSecret'  =>  '5kwk0of79j8ksgw8c48csgo0so8o8ccs00ssows40c4wc8osg8',
        'redirectUri'   =>  'http://stamplia-client-test.local/index.php'
    ));

    $client = new \Kiwup\StampliaClient\Client\Api($provider);

The methods you can use are the following:

    $client->createUser('email', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat');

    $client->getUser('id');

    $client->getUserMe();

    $client->putUser('id', 'email', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat');

    $client->getCategories();

    $client->getCategory('name');

    $client->getCategoryTemplates('category_name');

    $client->getTemplates('page', 'per_page', 'order', 'dir');

    $client->getTemplate('slug');

    $client->postZip('userId', 'file');

    $client->getUserTemplates('userId');

    $client->getUserTemplate('userId', 'templateId');

    $client->postUserTemplate('userId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'color_codes', 'category');

    $client->getUserPurchases('userId');

    $client->getUserPurchase('userId', 'purchaseId');

    $client->postUserPurchase('userId', 'coupon');

    $client->makePayment('userId', 'invoiceId', 'method', 'redirect_uri');

    $client->putUserTemplate'userId','templateId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'color_codes', 'category');

    $client->postCart('user', 'coupon', 'templates');

    $client->putCart('id', 'coupon', 'templates');

    $client->deleteCart('id');

    $client->getCart('id');


Please read http://dev.stamplia.com for the specific parameters that each method accepts and their results