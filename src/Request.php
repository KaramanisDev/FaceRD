<?php

namespace KaramanisWeb\FaceRD;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use KaramanisWeb\FaceRD\Contracts\RequestInterface;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Models\Data;

class Request implements RequestInterface
{
    protected $client;
    protected $apiBase;
    protected $resource;
    protected $credentials;
    protected $lastResponse;

    public function __construct(array $credentials = [], string $apiBase = '', string $resource = '')
    {
        $this->apiBase = $apiBase;
        $this->resource = $resource;
        $this->credentials = $credentials;
        $this->client = new Client([
            //'headers' => ['Accept' => 'application/json'],
            'http_errors' => false
        ]);
    }

    public function setApiBase(string $apiBase): void
    {
        $this->apiBase = $apiBase;
    }

    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }

    public function sent(string $method, array $data = [], array $options = []): Response
    {
        $data = array_merge($this->credentials, $data);

        $methodSplit = explode(':', $method);
        if (isset($methodSplit[1]) && $methodSplit[1] === 'multipart') {
            $multipartData = [];
            foreach ($data as $key => $value) {
                $multipartData[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            $options['multipart'] = $multipartData;
        } elseif ($methodSplit[0] === 'GET') {
            $options['query'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        return $this->lastResponse = $this->client->request($methodSplit[0], $this->prepareURL(), $options);
    }

    protected function prepareURL(): string
    {
        return rtrim($this->apiBase, '/') . '/' . ltrim($this->resource, '/');
    }

    public function getData(): Data
    {
        return $this->bodyToData($this->lastResponse);
    }

    public function bodyToData(Response $response): Data
    {
        return new Data(array_merge(['statusCode' => $response->getStatusCode()], $this->bodyToArray($response)));
    }

    public function bodyToArray(Response $response): array
    {
        return json_decode($response->getBody()->getContents(), 1) ?? [];
    }

    public function hasResponseFailed(Response $response, bool $throwException = false): bool
    {
        //REVIEW: this function later
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 201 && $statusCode !== 200) {
            if ($throwException) {
                throw new failedRequest($response->getReasonPhrase() . PHP_EOL . $response->getBody());
            }
            return true;
        }
        return false;
    }
}