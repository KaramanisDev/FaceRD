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

    public function __call($method, $parameters)
    {
        if(!is_callable(['Group',$method])){
            throw new notSupported();
        }
        return $this->$method(...$parameters);
    }

    protected function mapGroup($data): FaceGroup
    {
        $data = $data instanceof Data ? $data->toArray() : $data;
        return new FaceGroup(null, null, null, $data);
    }

    protected function mapGroups($data): array
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