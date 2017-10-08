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

    public function get(string $property)
    {
        return $this->{$property};
    }

    public function toArray(): array
    {
        $objectArray = [];
        $variables = get_class_vars(get_class($this));
        foreach ($variables as $key => $value) {
            $objectArray[$key] = $this->{$key};
        }
        return array_merge($objectArray, json_decode(json_encode($this), true));
    }

    public function toJson($opt = 0)
    {
        return json_encode($this->toArray(), $opt);
    }
}