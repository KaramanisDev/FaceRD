<?php

namespace KaramanisWeb\FaceRD\Drivers\Kairos;

use KaramanisWeb\FaceRD\Contracts\DriverInterface;
use KaramanisWeb\FaceRD\Drivers\AbstractDriver;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Exceptions\notSupported;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\Face;
use KaramanisWeb\FaceRD\Models\Result;
use KaramanisWeb\FaceRD\Utilities\Helpers;
use KaramanisWeb\FaceRD\Utilities\InputEnum;

class Driver extends AbstractDriver implements DriverInterface
{
    protected $driver = 'Kairos';
    protected $apiBase = 'https://api.kairos.com';
    protected $requiredCredentials = ['app_id', 'app_key'];
    protected $headerAuth = true;

    public function detect($input, array $options = []): array
    {
        if (!in_array(Helpers::getInputType($input), [InputEnum::URL, InputEnum::BASE64], true)) {
            throw new notSupported('Only url & base64 image supported as input.');
        }

        $this->request->setResource('detect');
        $this->request->sent('POST:json', [
            'image' => $input,
            'minHeadScale' => $options['minHeadScale'] ?? '.015',
            'selector' => $options['selector'] ?? 'FRONTAL',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapFaces($data->toArray());
    }

    public function recognize($input, string $group, array $options = []): Result
    {
        if (!in_array(Helpers::getInputType($input), [InputEnum::URL, InputEnum::BASE64], true)) {
            throw new notSupported('Only url & base64 image supported as input.');
        }

        $this->request->setResource('recognize');
        $this->request->sent('POST:json', [
            'image' => $input,
            'gallery_name' => $group,
            'minHeadScale' => $options['minHeadScale'] ?? '.015',
            'threshold' => $options['threshold'] ?? '0.70',
            'max_num_results' => $options['max_num_results'] ?? '10',
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapRecognize($data->toArray());
    }

    protected function mapRecognize($data): Result
    {
        $result = new Result($this->driver, uniqid('', true));
        $result->setMatches($data['images'] ?? []);
        unset($data['images']);
        $result->setUnmapped($data);
        return $result;
    }

    protected function mapFaces($data): array
    {
        $faces = [];
        foreach ((array)$data['images'][0]['faces'] as $face) {
            $faces[] = $this->mapFace($face);
        }
        return $faces;
    }

    protected function mapFace($data): Face
    {
        $face = new Face($this->driver, uniqid('', true));
        $face->setAttributes($data['attributes'] ?? []);
        $face->setRectangle($data['topLeftX'], $data['topLeftY'], $data['width'], $data['height']);
        $face->setUnmapped([
            'face_id' => $data['face_id'],
            'roll' => $data['roll']
        ]);
        $face->setLandmark(Helpers::arrayExcept($data,
            ['attributes', 'topLeftX', 'topLeftY', 'width', 'height', 'face_id', 'roll']));
        return $face;
    }

    protected function handleErrors(Data $data)
    {
        if (isset($data->{'Errors'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'Errors'}[0]['Message'] ?? 'Something went wrong!');
        }
    }
}