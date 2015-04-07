stamplia-php
============

A PHP library to access the stamplia API


Usage
-----

Add the following to your composer.json file

        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/Stamplia/stamplia-php.git"
            }
        ],
        "require": {
            "php": ">=5.3.3",
            "Stamplia/stamplia-php": "dev-master"
        },
        "minimum-stability": "dev",
        "config": {
            "process-timeout": 3600
        }

Then in your PHP file, require the composer autoloader, something similar to this

    <?php
    require('vendor/autoload.php');

You can then create a OAuth provider with your app data, create a new API client, and use it:

    $provider = new Stamplia\StampliaClient\Provider\Stamplia(array(
        'clientId'  =>  '12_1fo52xvb0k1wcsck0oc88o8cos8gw44w8gksc0okgcks4gc40g',
        'clientSecret'  =>  '5kwk0of79j8ksgw8c48csgo0so8o8ccs00ssows40c4wc8osg8',
        'redirectUri'   =>  'http://stamplia-client-test.local/index.php'
    ));

    $client = new \Stamplia\StampliaClient\Client\Api($provider);

Get the access token and save it to your database for example for later use

    $access_token = $client->getAccessToken();
    //TODO save access token for this user to local database

The methods you can use are the following:

    $client->createUser(array('email', 'alias', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'));

    $client->getUser(array('id'));

    $client->getUserMe();

    $client->putUser(array('id', 'alias', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'));

    $client->getCategories(array('top_level'));

    $client->getCategory(array('name'));
    
    $client->getThemes();
    
    $client->getTheme(array('name'));

    $client->getCategoryTemplates(array('name', 'page', 'per_page', 'order', 'dir'));

    $client->getTemplates(array('page', 'per_page', 'order', 'dir', 'category'));

    $client->getTemplate(array('slug'));
    
    $client->getTemplateLitmustests(array('id'));

    $client->postZip(array('userId', 'file'));

    $client->getUserTemplates(array('userId'));

    $client->getUserTemplate(array('userId', 'templateId'));

    $client->postUserTemplate(array('userId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'category'));

    $client->getUserPurchases(array('userId'));

    $client->getUserPurchase(array('userId', 'purchaseId'));

    $client->postUserPurchase(array('userId', 'coupon'));
    
    $client->downloadUserPurchase(array('userId', 'purchaseId', 'format'));

    $client->makePayment(array('userId', 'invoiceId', 'method', 'redirect_uri'));
    
    $client->getPayment(array('userId', 'invoiceId'));

    $client->putUserTemplate(array('userId','templateId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'category'));

    $client->postCart(array('user', 'coupon', 'templates'));

    $client->putCart(array('id', 'coupon', 'templates'));

    $client->deleteCart(array('id'));

    $client->getCart(array('id'));


Please read http://api.stamplia.com/documentation/getting_started/ for the specific parameters that each method accepts and their results
