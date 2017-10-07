<?php

namespace KaramanisWeb\FaceRD\Models;

use KaramanisWeb\FaceRD\Utilities\Helpers;

class FaceGroup extends AbstractModel
{
    protected $driver;
    protected $token;
    protected $name;
    protected $faces;
    protected $tags;
    protected $unmapped;

    public function __construct(string $driver, string $token, string $name = null, array $unmapped = [])
    {
        $this->driver = $driver;
        $this->token = $token;
        $this->name = $name;
        $this->unmapped = $unmapped;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setFaces($faces)
    {
        $this->faces = is_array($faces) || null === $faces ? $faces : Helpers::stringArray($faces);
    }

    public function setTags($tags)
    {
        $this->tags = is_array($tags) || null === $tags ? $tags : Helpers::stringArray($tags);
    }
}