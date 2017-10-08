<?php

namespace KaramanisWeb\FaceRD\Drivers;

use KaramanisWeb\FaceRD\Contracts\GroupInterface;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Exceptions\notSupported;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\Face;
use KaramanisWeb\FaceRD\Models\Result;
use KaramanisWeb\FaceRD\Request;
use KaramanisWeb\FaceRD\Utilities\Helpers;

abstract class AbstractDriver
{
    protected $driver;
    protected $apiBase;
    protected $request;
    protected $requiredCredentials = [];
    protected $headerAuth = false;

    public function __construct(array $credentials)
    {
        if ($this->driver === null || empty($this->driver)) {
            throw new \InvalidArgumentException('The property $driver must not be null or empty');
        }
        if ($this->apiBase === null || empty($this->apiBase)) {
            throw new \InvalidArgumentException('The property $apiBase must not be null or empty');
        }
        if (!is_array($this->requiredCredentials)) {
            throw new \InvalidArgumentException('The property $requiredCredentials must be an array ex: [\'api_key\', \'api_secret\']');
        }
        if (!Helpers::arrayKeysExists($credentials, $this->requiredCredentials)) {
            throw new \InvalidArgumentException('The credentials must contain the following parameters: ' . Helpers::arrayString($this->requiredCredentials, ', ') . '.');
        }
        $this->request = new Request($this->apiBase);
        $this->request->setCredentials($credentials, $this->headerAuth);
    }

    public function getRequiredCredentials(): array
    {
        return $this->requiredCredentials;
    }

    public function group(): GroupInterface
    {
        $groupClass = sprintf('KaramanisWeb\\FaceRD\\Drivers\\%s\\Group', $this->driver);
        if (!class_exists($groupClass)) {
            throw new notSupported(get_class($this) . ' group faces is not supported with this driver.');
        }
        return new $groupClass($this->request);
    }

    protected function mapCompare($data): Result
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new Result('', '', $data);
    }

    protected function mapRecognise($data): Result
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new Result('', '', $data);
    }

    protected function mapFace($data): Face
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new Face('', '', $data);
    }

    protected function mapFaces($data): array
    {
        return $data instanceof Data ? $data->toArray() : $data;
    }

    protected function handleErrors(Data $data): void
    {
        if ($data->statusCode !== 200 && $data->statusCode !== 201) {
            throw new failedRequest('Something went wrong!');
        }
    }
}