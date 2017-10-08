<?php

namespace KaramanisWeb\FaceRD\Models;

class Result extends AbstractModel
{
    protected $driver;
    protected $token;
    protected $confidence;
    protected $matches;

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

    public function setMatches(array $matches)
    {
        $this->matches = $matches;
    }
}