<?php

namespace KaramanisWeb\FaceRD\Drivers\Kairos;

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
        $this->request->setResource('gallery/list_all');
        $this->request->sent('POST', $options);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapGroups($data->toArray());
    }

    public function get(string $group, array $options = []): FaceGroup
    {
        $this->request->setResource('gallery/view');
        $this->request->sent('POST:json', array_merge([
            'gallery_name' => $group,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        $data->name = $group;
        return $this->mapGroup($data->toArray());
    }

    public function create(string $group, array $options = []): string
    {
        return 'There is no need to create a gallery just add a face and it wil be auto created!';
    }

    public function delete(string $group, array $options = []): bool
    {
        $this->request->setResource('gallery/remove');
        $this->request->sent('POST:json', array_merge([
            'gallery_name' => $group,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return true;
    }

    public function addFace($input, string $group, array $options = []): string
    {
        if (!in_array(Helpers::getInputType($input), [InputEnum::URL, InputEnum::BASE64], true)) {
            throw new notSupported('Only url & base64 image supported as input.');
        }
        $faceID = uniqid();
        $this->request->setResource('enroll');
        $this->request->sent('POST:json', [
            'image' => $input,
            'subject_id' => $faceID,
            'gallery_name' => $group,
            'minHeadScale' => $options['minHeadScale'] ?? '.015',
            'multiple_faces' => $options['multiple_faces'] ?? 'false',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $faceID;
    }

    public function removeFace($input, string $group, array $options = []): bool
    {
        if (Helpers::getInputType($input) !== InputEnum::TOKEN) {
            throw new notSupported('Only face tokens are supported can be either one or multiple.');
        }
        $this->request->setResource('gallery/remove_subject');
        $this->request->sent('POST:json', [
            'gallery_name' => $group,
            'subject_id' => $input
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return true;
    }

    protected function mapGroups($data): array
    {
        $groups = [];
        foreach ((array)$data['gallery_ids'] as $key => $value) {
            $group['name'] = $value;
            $groups[] = $this->mapGroup($group);
        }
        return $groups;
    }

    protected function mapGroup($data): FaceGroup
    {
        $group = new FaceGroup(Helpers::getDriver($this), uniqid('', true), $data['name']);
        $group->setFaces($data['subject_ids'] ?? []);
        $group->setUnmapped(Helpers::arrayExcept($data, ['name', 'subject_ids']));
        return $group;
    }

    protected function handleErrors(Data $data): void
    {
        if (isset($data->{'Errors'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'Errors'}[0]['Message'] ?? 'Something went wrong!');
        }
    }
}