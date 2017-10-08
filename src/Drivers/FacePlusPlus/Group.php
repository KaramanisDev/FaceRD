<?php

namespace KaramanisWeb\FaceRD\Drivers\FacePlusPlus;

use KaramanisWeb\FaceRD\Contracts\GroupInterface;
use KaramanisWeb\FaceRD\Drivers\AbstractGroup;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\FaceGroup;
use KaramanisWeb\FaceRD\Utilities\Helpers;

class Group extends AbstractGroup implements GroupInterface
{
    public function list(array $options = []): array
    {
        $this->request->setResource('faceset/getfacesets');
        $this->request->sent('POST', $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroups($data);
    }

    public function get($nameOrToken, bool $isToken = false, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/getdetail');
        $this->request->sent('POST', array_merge([
            $isToken ? 'faceset_token' : 'outer_id' => $nameOrToken,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function create(string $name, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/create');
        $this->request->sent('POST', [
            'display_name' => $name,
            'outer_id' => $name,
            'tags' => $options['tags'] ?? '',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function update($nameOrToken, bool $isToken = false, array $data, array $options = []): FaceGroup
    {
        $groupData = [
            $isToken ? 'faceset_token' : 'outer_id' => $nameOrToken
        ];
        if (isset($data['name'])) {
            $groupData['new_outer_id'] = $data['name'];
            $groupData['display_name'] = $data['name'];
        }
        if (isset($data['tags'])) {
            $groupData['tags'] = $data['tags'];
        }

        $this->request->setResource('faceset/update');
        $this->request->sent('POST', array_merge($groupData, $options));
        $results = $this->request->getData();

        $this->handleErrors($results);
        return $this->mapGroup($results->toArray());
    }

    public function delete($nameOrToken, bool $isToken = false, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/delete');
        $this->request->sent('POST', array_merge([
            $isToken ? 'faceset_token' : 'outer_id' => $nameOrToken,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function addFace($nameOrToken, bool $isToken = false, $faceTokens)
    {
        $this->request->setResource('faceset/addface');
        $this->request->sent('POST', [
            $isToken ? 'faceset_token' : 'outer_id' => $nameOrToken,
            'face_tokens' => Helpers::arrayString($faceTokens)
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function removeFace($nameOrToken, bool $isToken = false, $faceTokens)
    {
        $this->request->setResource('faceset/removeface');
        $this->request->sent('POST', [
            $isToken ? 'faceset_token' : 'outer_id' => $nameOrToken,
            'face_tokens' => Helpers::arrayString($faceTokens)
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    protected function mapGroup($data): FaceGroup
    {
        $group = new FaceGroup(Helpers::getDriver($this), $data['faceset_token']);
        $group->setName($data['display_name'] ?? $data['outer_id']);
        $group->setTags($data['tags'] ?? null);
        $group->setFaces($data['face_tokens'] ?? null);
        $group->setUnmapped(Helpers::arrayExcept($data,
            ['faceset_token', 'outer_id', 'display_name', 'tags', 'face_tokens']));
        return $group;
    }

    protected function mapGroups($data): array
    {
        $groups = [];
        foreach ((array)$data->{'facesets'} as $faceset) {
            $groups[] = $this->mapGroup($faceset);
        }
        return $groups;
    }

    protected function handleErrors(Data $data): void
    {
        if (($data->statusCode !== 200 && $data->statusCode !== 201) || isset($data->{'error_message'})) {
            throw new failedRequest($data->{'error_message'} ?? 'Something went wrong!');
        }
    }
}