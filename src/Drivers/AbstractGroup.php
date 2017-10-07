<?php

namespace KaramanisWeb\FaceRD\Drivers;

use KaramanisWeb\FaceRD\Contracts\RequestInterface;
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

    public function list(array $options = [])
    {
        throw new notSupported();
    }

    public function get($nameOrToken, bool $isToken = false, array $options = [])
    {
        throw new notSupported();
    }

    public function create(string $name, array $options = [])
    {
        throw new notSupported();
    }

    public function update($nameOrToken, bool $isToken = false, array $data, array $options = [])
    {
        throw new notSupported();
    }

    public function delete($nameOrToken, bool $isToken = false, array $options = [])
    {
        throw new notSupported();
    }

    public function mapGroups($data): array
    {
        return $data instanceof Data ? $data->toArray() : $data;
    }

    public function mapGroup($data): FaceGroup
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new FaceGroup(null, null, null, $data);
    }

    public function addFace($nameOrToken, bool $isToken = false, $faceTokens)
    {
        throw new notSupported();
    }

    public function removeFace($nameOrToken, bool $isToken = false, $faceTokens)
    {
        throw new notSupported();
    }
}