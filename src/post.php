<?php

use AcMarche\ApiWp\ApiClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

require_once '../../../wp-load.php';
require '../../../vendor/autoload.php';

$apiClient = new ApiClient();
try {
    /*  $apiClient->createPost([
          'title'      => 'zeze22',
          'content'      => 'zeze22',
          'excpert'      => 'zeze22',
          'status'     => 'publish',
          'categories' => [133],
      ], 9867);*/
} catch (Exception | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
    var_dump($exception->getMessage());
}

try {
    $apiClient->createAttachement([
        'title'  => 'zeze22',
        'status' => 'publish',
        'post'   => [9867],
    ], 9867);
} catch (Exception | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
    var_dump($exception->getMessage());
}
