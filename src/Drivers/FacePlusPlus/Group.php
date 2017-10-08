<?php

namespace KaramanisWeb\FaceRD\Drivers\FacePlusPlus;

use KaramanisWeb\FaceRD\Contracts\GroupInterface;
use KaramanisWeb\FaceRD\Drivers\AbstractGroup;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Exceptions\notSupported;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\FaceGroup;
use KaramanisWeb\FaceRD\Utilities\Helpers;
use KaramanisWeb\FaceRD\Utilities\InputEnum;

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

    public function get(string $group, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/getdetail');
        $this->request->sent('POST', array_merge([
            'outer_id' => $group,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function create(string $group, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/create');
        $this->request->sent('POST', [
            'display_name' => $group,
            'outer_id' => $group,
            'tags' => $options['tags'] ?? '',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function update(string $group, array $data, array $options = []): FaceGroup
    {
        $groupData = [
            'outer_id' => $group
        ];
        if (isset($data['name'])) {
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

    public function delete(string $group, array $options = []): FaceGroup
    {
        $this->request->setResource('faceset/delete');
        $this->request->sent('POST', array_merge([
            'outer_id' => $group,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function addFace($input, string $group, array $options = [])
    {
        if (Helpers::getInputType($input) !== InputEnum::TOKEN) {
            throw new notSupported('Only face tokens are supported can be either one or multiple.');
        }
        $this->request->setResource('faceset/addface');
        $this->request->sent('POST', array_merge([
            'outer_id' => $group,
            'face_tokens' => Helpers::arrayString($input)
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function removeFace($input, string $group, array $options = [])
    {
        if (Helpers::getInputType($input) !== InputEnum::TOKEN) {
            throw new notSupported('Only face tokens are supported can be either one or multiple.');
        }
        $this->request->setResource('faceset/removeface');
        $this->request->sent('POST', array_merge([
            'outer_id' => $group,
            'face_tokens' => Helpers::arrayString($input)
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    protected function mapGroup($data): FaceGroup
    {
        $group = new FaceGroup(Helpers::getDriver($this), $data['outer_id']);
        $group->setName($data['display_name'] ?? $data['outer_id']);
        $group->setTags($data['tags'] ?? null);
        $group->setFaces($data['face_tokens'] ?? null);
        $group->setUnmapped(Helpers::arrayExcept($data, ['outer_id', 'display_name', 'tags', 'face_tokens']));
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
        if (isset($data->{'error_message'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'error_message'} ?? 'Something went wrong!');
        }
    }
}