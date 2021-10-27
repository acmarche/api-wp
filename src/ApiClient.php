<?php

namespace AcMarche\ApiWp;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    private ?HttpClientInterface $httpClient = null;
    private string $url;

    public function __construct()
    {
    }

    public function connect()
    {
        $this->url = $_ENV['WP_SITE'].'/wp-json/wp/v2';
        $options = new HttpOptions();
        $options->setAuthBasic($_ENV['WP_USER'], $_ENV['WP_PASSWORD']);
        $this->httpClient = HttpClient::createForBaseUri($this->url, $options->toArray());
    }

    public function req()
    {
        if (!$this->httpClient) {
            $this->connect();
        }
        $response = $this->httpClient->request('GET', $this->url.'/posts');
        $statut = $response->getStatusCode();
        if ($statut === Response::HTTP_OK) {
            $content = $response->getContent();
            var_dump($content);
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function createPost(array $data, ?int $postId = null)
    {
        if (!$this->httpClient) {
            $this->connect();
        }
        $url = $this->url.'/posts';
        if ($postId) {
            $url .= '/'.$postId;
        }
        $response = $this->httpClient->request('POST', $url, [
            'body' => $data,
        ]);
        $httpLogs = $response->getInfo('response_headers');
        dump($httpLogs);
        $statut = $response->getStatusCode();
        dump($statut);
        $content = $response->getContent();

        dump($content);
    }

    public function createAttachement(array $data, ?int $postId = null)
    {
        if (!$this->httpClient) {
            $this->connect();
        }
        $url = $this->url.'/media';
        if ($postId) {
            $url .= '/'.$postId;
        }
        $response = $this->httpClient->request('POST', $url, [
            'body' => $data,
        ]);
        $httpLogs = $response->getInfo('response_headers');
        dump($httpLogs);
        $statut = $response->getStatusCode();
        dump($statut);
        $content = $response->getContent();

        dump($content);
    }
}
