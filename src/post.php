<?php

use AcMarche\ApiWp\ApiClient;

require_once '../../../wp-load.php';
require '../../../vendor/autoload.php';

$apiClient = new ApiClient();
try {
    $apiClient->post([
        'title' => 'zeze',
        'status'=>'publish',
        'categories'=>[133]
    ]);
}
catch ( Exception $exception) {
    var_dump($exception->getMessage());
}


