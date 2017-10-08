<?php

namespace KaramanisWeb\FaceRD\Contracts;

use KaramanisWeb\FaceRD\Models\Result;

interface DriverInterface
{
    public function group(): GroupInterface;

    public function detect($input, array $options = []): array;

    public function compare($input1, $input2): Result;

    public function recognise($input, string $group, bool $groupIsToken = false, array $options = []): Result;
}