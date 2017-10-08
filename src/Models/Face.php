<?php

namespace KaramanisWeb\FaceRD\Models;

class Face extends AbstractModel
{
    protected $driver;
    protected $token;
    protected $rectangle;
    protected $attributes;
    protected $landmark;

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

    public function setLandmark($landmark)
    {
        $this->landmark = $landmark;
    }

    public function setRectangle(int $left, int $top, int $width, int $height): void
    {
        $this->rectangle = [
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
        ];
    }
}