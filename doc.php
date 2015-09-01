<?php

require_once 'vendor/autoload.php';

use \Stamplia\ApiClient;

echo 'Stamplia API version: '.ApiClient::DEFAULT_API_VERSION.PHP_EOL;
echo PHP_EOL;

$methods = ApiClient::publicMethods();
foreach ($methods as $name  => $method) {
    $title = '* ApiClient::'.$name.'()'. ' *';

    echo str_repeat('*', strlen($title)).PHP_EOL;
    echo $title.PHP_EOL;
    echo str_repeat('*', strlen($title)).PHP_EOL;

    if (!$method['anonymous']) {
        echo '-- Requires login first --'.PHP_EOL;
    }

    if (isset($method['description'])) {
        echo 'Description: '.$method['description'].PHP_EOL;
    }

    echo ' Parameters: '.json_encode($method['parameters']).PHP_EOL;
    echo PHP_EOL.PHP_EOL;
}
