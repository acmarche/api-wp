<?php

namespace AcMarche\ApiWp;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

    /**
     * @throws  \Exception|\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getPostsByCategory(int $categoryId): ?string
    {
        if (!$this->httpClient) {
            $this->connect();
        }
        $response = $this->httpClient->request('GET', $this->url.'/posts/'.$categoryId);

        return $this->getContent($response);
    }

    /**
     * @throws  \Exception|\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createPost(array $data, ?int $postId = null): ?string
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

        return $this->getContent($response);
    }


    /**
     * @throws \Exception|\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createMedia(string $fileName, string $type, string $data, ?int $postId = null): ?string
    {
        if (!$this->httpClient) {
            $this->connect();
        }
        $url = $this->url.'/media';
        $dataPart = new DataPart($data, $fileName, $type);

        $formFields = [
            'title' => 'some value2',
            'alt_text' => 'lilou',
            'post' => (string)$postId,
            'file' => $dataPart,
        ];

        $formData = new FormDataPart($formFields);

        $headers = [
            'headers' => [
                'Content-Disposition' => "attachment; filename=%s".$fileName,
                'Content-Type' => $type,
            ],
            'body' => $data,
        ];

        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $this->getContent($response);
    }

    /**
     * @throws \Exception
     */
    public function getContent(ResponseInterface $request): ?string
    {
        //$statusCode = $request->getStatusCode();

        try {
            return $request->getContent();
        } catch (ClientExceptionInterface | TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            throw  new \Exception($e->getMessage());
        }
    }
}
