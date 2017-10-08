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

    public function __construct(string $apiBase = '', string $resource = '')
    {
        $this->apiBase = $apiBase;
        $this->resource = $resource;
        $this->client = new Client([
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

    public function setCredentials(array $credentials, bool $headerAuth = false): void
    {
        $this->credentials = $credentials;
        $this->credentials['header_auth'] = $headerAuth;
    }

    public function sent(string $method, array $data = [], array $options = []): Response
    {
        $this->prepareCredentials($data, $options);

        $methodSplit = explode(':', $method);
        if (isset($methodSplit[1])) {
            switch ($methodSplit[1]) {
                case 'raw':
                    $options['body'] = $data[0];
                    break;
                case 'multipart':
                    $multipartData = [];
                    foreach ($data as $key => $value) {
                        $multipartData[] = [
                            'name' => $key,
                            'contents' => $value
                        ];
                    }
                    $options['multipart'] = $multipartData;
                    break;
                case 'json':
                    $options['json'] = $data;
                    break;
                default:
                    throw new failedRequest('Invalid method type, choose between: raw, multipart & json.');
            }
        } elseif ($methodSplit[0] === 'GET') {
            $options['query'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        return $this->lastResponse = $this->client->request($methodSplit[0], $this->prepareURL(), $options);
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
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 201 && $statusCode !== 200) {
            if ($throwException) {
                throw new failedRequest($response->getReasonPhrase() . PHP_EOL . $response->getBody());
            }
            return true;
        }
        return false;
    }

    protected function prepareCredentials(array &$data, array &$options): void
    {
        if ($this->credentials['header_auth']) {
            $options['headers'] = array_merge($this->credentials, $options['headers'] ?? []);
        } else {
            $data = array_merge($this->credentials, $data);
        }
    }

    protected function prepareURL(): string
    {
        return rtrim($this->apiBase, '/') . '/' . ltrim($this->resource, '/');
    }
}