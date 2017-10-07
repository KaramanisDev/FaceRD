<?php

namespace KaramanisWeb\FaceRD\Models;

class Face extends AbstractModel
{
    protected $driver;
    protected $token;
    protected $rectangle;
    protected $attributes;
    protected $unmapped;

    public function __construct(string $driver, string $token, array $unmapped = [])
    {
        $this->driver = $driver;
        $this->token = $token;
        $this->unmapped = $unmapped;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function setRectangle(int $x, int $y, int $width, int $height): void
    {
        $this->rectangle = [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];
    }
}