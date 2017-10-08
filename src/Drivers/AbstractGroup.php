<?php

namespace KaramanisWeb\FaceRD\Drivers;

use KaramanisWeb\FaceRD\Contracts\RequestInterface;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Exceptions\notSupported;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\FaceGroup;

abstract class AbstractGroup
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function list(array $options = []): array
    {
        throw new notSupported();
    }

    public function get(string $group, array $options = []): FaceGroup
    {
        throw new notSupported();
    }

    public function create(string $group, array $options = [])
    {
        throw new notSupported();
    }

    public function update(string $group, array $data, array $options = [])
    {
        throw new notSupported();
    }

    public function delete(string $group, array $options = [])
    {
        throw new notSupported();
    }

    public function addFace($input, string $group, array $options = [])
    {
        throw new notSupported();
    }

    public function removeFace($input, string $group, array $options = [])
    {
        throw new notSupported();
    }

    protected function mapGroup($data): FaceGroup
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new FaceGroup('', '', '', $data);
    }

    protected function mapGroups($data): array
    {
        return $data instanceof Data ? $data->toArray() : $data;
    }

    protected function handleErrors(Data $data): void
    {
        if ($this->failedDataStatus($data)) {
            throw new failedRequest('Something went wrong!');
        }
    }

    protected function failedDataStatus(Data $data): bool
    {
        return $data->statusCode !== 200 && $data->statusCode !== 201;
    }
}