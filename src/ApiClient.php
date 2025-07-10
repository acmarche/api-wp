<?php

namespace AcMarche\ApiWp;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(
        #[Autowire('%env(WP_SITE)%')]
        private readonly string $wpSite,
        #[Autowire('%env(WP_USER)%')]
        private readonly string $wpUser,
        #[Autowire('%env(WP_PASSWORD)%')]
        private readonly string $wpPassword,
    ) {
    }

    public function connect(): void
    {
        $this->url = $this->wpSite.'/wp-json/wp/v2';
        $options = new HttpOptions();
        $options->setAuthBasic($this->wpUser, $this->wpPassword);
        $this->httpClient = HttpClient::createForBaseUri($this->url, $options->toArray());
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function getPostsByCategory(int $categoryId): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->connect();
        }
        $args = [
            'categories' => $categoryId,
            'per_page' => 50,
            'orderby' => 'date',
            'order' => 'desc',
        ];
        $response = $this->httpClient->request('GET', $this->url.'/posts', [
            'query' => $args,
        ]);

        //dump($response->getInfo());
        return $this->getContent($response);
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function getPost(int $postId): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->connect();
        }
        $response = $this->httpClient->request('GET', $this->url.'/posts/'.$postId, [
        ]);

        return $this->getContent($response);
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function getCategories(array $include): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->connect();
        }
        $response = $this->httpClient->request('GET', $this->url.'/categories', [
            'query' => [
                'include' => implode(',', $include),
            ],
        ]);

        return $this->getContent($response);
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function createPost(array $data, ?int $postId = null): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
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
     * @throws Exception|TransportExceptionInterface
     */
    public function createMedia(string $fileName, string $type, string $data, ?int $postId = null): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->connect();
        }
        $url = $this->url.'/media';
        $dataPart = new DataPart($data, $fileName, $type);

        $formFields = [
            // 'title' => 'some value2',
            // 'alt_text' => 'lilou',
            'file' => $dataPart,
        ];
        if ($postId) {
            $formFields['post'] = (string)$postId;
        }
        $formData = new FormDataPart($formFields);

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
     * @throws Exception|TransportExceptionInterface
     */
    public function deletePost(int $postId): ?string
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->connect();
        }
        $url = $this->url.'/posts/'.$postId;

        $response = $this->httpClient->request('DELETE', $url, [
        ]);

        /*   dump($response);
           dump($response->getInfo());
           dump($response->getContent(false));*/

        return $this->getContent($response);
    }

    /**
     * @throws Exception
     */
    public function getContent(ResponseInterface $request): ?string
    {
        try {
            $statusCode = $request->getStatusCode();

            return $request->getContent(Response::HTTP_OK === $statusCode);
        } catch (ClientExceptionInterface|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
