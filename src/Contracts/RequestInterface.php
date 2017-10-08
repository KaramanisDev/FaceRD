<?php

namespace KaramanisWeb\FaceRD\Contracts;

use GuzzleHttp\Psr7\Response;
use KaramanisWeb\FaceRD\Models\Data;

interface RequestInterface
{
    public function setApiBase(string $apiBase): void;

    public function setResource(string $resource): void;

    public function setCredentials(array $credentials, bool $headerAuth = false): void;

    public function sent(string $method, array $data = [], array $options = []): Response;

    public function getData(): Data;

    public function bodyToData(Response $response): Data;

    public function bodyToArray(Response $response): array;

    public function hasResponseFailed(Response $response, bool $throwException = false): bool;
}