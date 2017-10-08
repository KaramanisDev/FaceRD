<?php

namespace KaramanisWeb\FaceRD\Models;

class Data extends AbstractModel
{
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
    }
}