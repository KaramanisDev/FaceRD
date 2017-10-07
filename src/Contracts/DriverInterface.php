<?php

namespace KaramanisWeb\FaceRD\Contracts;

use KaramanisWeb\FaceRD\Models\Face;

interface DriverInterface
{
    public function group(): GroupInterface;

    public function detect($input, array $options = []): array;

    public function compare($input1, $input2);

    public function recognise($input, string $group, bool $groupIsToken = false, array $options = []);

    public function mapFaces($data): array;

    public function mapFace($data): Face;
}