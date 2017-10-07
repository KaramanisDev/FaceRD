<?php

namespace KaramanisWeb\FaceRD\Models;

abstract class AbstractModel
{
    public function setUnmapped(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->unmapped[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return (array)$this;
    }

    public function toJson($opt = 0)
    {
        return json_encode($this, $opt);
    }
}