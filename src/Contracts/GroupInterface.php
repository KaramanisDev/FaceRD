<?php

namespace KaramanisWeb\FaceRD\Contracts;

use KaramanisWeb\FaceRD\Models\FaceGroup;

interface GroupInterface
{
    public function list(array $options = []);

    public function get($nameOrToken, bool $isToken = false, array $options = []);

    public function create(string $name, array $options = []);

    public function update($nameOrToken, bool $isToken = false, array $data, array $options = []);

    public function delete($nameOrToken, bool $isToken = false, array $options = []);

    public function mapGroups($data): array;

    public function mapGroup($data): FaceGroup;

    public function addFace($nameOrToken, bool $isToken = false, $faceTokens);

    public function removeFace($nameOrToken, bool $isToken = false, $faceTokens);
}