<?php

namespace KaramanisWeb\FaceRD\Contracts;

use KaramanisWeb\FaceRD\Models\FaceGroup;

interface GroupInterface
{
    public function list(array $options = []): array;

    public function get(string $group, array $options = []): FaceGroup;

    public function create(string $group, array $options = []);

    public function update(string $group, array $data, array $options = []);

    public function delete(string $group, array $options = []);

    public function addFace($input, string $group, array $options = []);

    public function removeFace($input, string $group, array $options = []);
}