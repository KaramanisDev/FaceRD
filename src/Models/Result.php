<?php

namespace KaramanisWeb\FaceRD\Models;

class Result extends AbstractModel
{
    protected $driver;
    protected $token;
    protected $confidence;

    public function __construct(string $driver, string $token, array $unmapped = [])
    {
        $this->driver = $driver;
        $this->token = $token;
        $this->unmapped = $unmapped;
    }

    public function setConfidence($confidence)
    {
        $this->confidence = $confidence;
    }
}