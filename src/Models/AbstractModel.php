<?php

namespace KaramanisWeb\FaceRD\Models;

abstract class AbstractModel
{
    protected $unmapped;

    public function setUnmapped(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->unmapped[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }

    public function toJson($opt = 0)
    {
        return json_encode($this, $opt);
    }
}