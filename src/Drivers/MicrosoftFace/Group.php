<?php

namespace KaramanisWeb\FaceRD\Drivers\MicrosoftFace;

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
        $this->request->setResource('facelists');
        $this->request->sent('GET', $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroups($data->toArray());
    }

    public function get(string $group, array $options = []): FaceGroup
    {
        $this->request->setResource('facelists/' . strtolower($group));
        $this->request->sent('GET', [], $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroup($data->toArray());
    }

    public function create(string $group, array $options = []): bool
    {
        $this->request->setResource('facelists/' . strtolower($group));
        $this->request->sent('PUT:json', [
            'name' => $group,
            'userData ' => $options['userData'] ?? '',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return true;
    }

    public function update(string $group, array $data, array $options = []): bool
    {
        $groupData = [];
        $groupData['name'] = $data['name'] ?? $group;
        if (isset($data['userData'])) {
            $groupData['userData'] = $data['userData'];
        }

        $this->request->setResource('facelists/' . strtolower($group));
        $this->request->sent('PATCH:json', array_merge($groupData, $options));
        $results = $this->request->getData();

        $this->handleErrors($results);
        return true;
    }

    public function delete(string $group, array $options = []): bool
    {
        $this->request->setResource('facelists/' . strtolower($group));
        $this->request->sent('DELETE', $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return true;
    }

    public function addFace($input, string $group, array $options = [])
    {
        if (Helpers::getInputType($input) !== InputEnum::URL) {
            throw new notSupported('Only image url is supported as input.');
        }

        $resource = 'facelists/' . $group . '/persistedFaces?userData=' . ($options['userData'] ?? '');
        if (isset($options['targetFace'])) {
            $resource .= '&targetFace=' . str_replace(' ', '', $options['targetFace']);
        }
        $this->request->setResource($resource);
        $this->request->sent('POST:json', [
            'url' => $input,
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $data->{'persistedFaceId'};
    }

    public function removeFace($input, string $group, array $options = [])
    {
        if (Helpers::getInputType($input) !== InputEnum::TOKEN) {
            throw new notSupported('Only face token is supported as input.');
        }

        $this->request->setResource('facelists/' . $group . '/persistedFaces/' . $input);
        $this->request->sent('DELETE', $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return true;
    }

    protected function mapGroup($data): FaceGroup
    {
        $group = new FaceGroup(Helpers::getDriver($this), $data['faceListId']);
        $group->setName($data['name'] ?? $data['outer_id']);
        $faces = [];
        foreach ((array)($data['persistedFaces'] ?? []) as $face) {
            $faces[] = $face['persistedFaceId'];
        }
        $group->setFaces($faces);
        $group->setUnmapped(Helpers::arrayExcept($data, ['faceListId', 'name', 'persistedFaces']));
        return $group;
    }

    protected function mapGroups($data): array
    {
        $groups = [];
        $data = Helpers::arrayExcept($data, ['statusCode', 'unmapped']);
        foreach ((array)$data as $group) {
            $groups[] = $this->mapGroup($group);
        }
        return $groups;
    }

    protected function handleErrors(Data $data)
    {
        if (isset($data->{'error'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'error'}['message'] ?? 'Something went wrong!');
        }
    }
}