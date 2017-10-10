<?php

namespace KaramanisWeb\FaceRD\Drivers\FacePlusPlus;

use KaramanisWeb\FaceRD\Contracts\DriverInterface;
use KaramanisWeb\FaceRD\Drivers\AbstractDriver;
use KaramanisWeb\FaceRD\Exceptions\failedRequest;
use KaramanisWeb\FaceRD\Models\Data;
use KaramanisWeb\FaceRD\Models\Face;
use KaramanisWeb\FaceRD\Models\Result;
use KaramanisWeb\FaceRD\Utilities\Helpers;
use KaramanisWeb\FaceRD\Utilities\InputEnum;

class Driver extends AbstractDriver implements DriverInterface
{
    protected $driver = 'FacePlusPlus';
    protected $apiBase = 'https://api-us.faceplusplus.com/facepp/v3';
    protected $requiredCredentials = ['api_key', 'api_secret'];

    public function detect($input, array $options = []): array
    {
        $type = Helpers::getInputType($input);

        $this->request->setResource('detect');
        $this->request->sent('POST:multipart', [
            $type === InputEnum::TOKEN ? 'face_token' : 'image_' . $type => $input,
            'return_landmark' => $options['landmark'] ?? '0',
            'return_attributes' => str_replace(' ', '', $options['attributes'] ?? 'none')
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapFaces($data);
    }

    public function compare($input1, $input2, array $options = []): Result
    {
        $type1 = Helpers::getInputType($input1);
        $type2 = Helpers::getInputType($input2);

        $this->request->setResource('compare');
        $this->request->sent('POST:multipart', array_merge([
            $type1 === InputEnum::TOKEN ? 'face_token1' : 'image_' . $type1 . '1' => $input1,
            $type2 === InputEnum::TOKEN ? 'face_token2' : 'image_' . $type1 . '2' => $input2,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapCompare($data);
    }

    public function recognize($input, string $group, array $options = []): Result
    {
        $type = Helpers::getInputType($input);

        $this->request->setResource('search');
        $this->request->sent('POST:multipart', array_merge([
            $type === InputEnum::TOKEN ? 'face_token' : 'image_' . $type => $input,
            'outer_id' => $group,
        ], $options));
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapRecognize($data->toArray());
    }

    protected function mapRecognize($data): Result
    {
        $result = new Result($this->driver, uniqid('', true));
        $result->setMatches($data['results']);
        unset($data['results']);
        $result->setUnmapped($data);
        return $result;
    }

    protected function mapCompare($data): Result
    {
        $result = new Result($this->driver, $data->{'request_id'});
        $result->setConfidence($data->{'confidence'});
        $result->setUnmapped(Helpers::arrayExcept($data->toArray(), ['request_id', 'confidence']));
        return $result;
    }

    protected function mapFace($data): Face
    {
        $face = new Face($this->driver, $data['face_token']);
        $face->setAttributes($data['attributes'] ?? []);
        $face->setLandmark($data['landmark'] ?? []);
        $rectangle = $data['face_rectangle'];
        $face->setRectangle($rectangle['left'], $rectangle['top'], $rectangle['width'], $rectangle['height']);
        $face->setUnmapped(Helpers::arrayExcept($data, ['face_token', 'attributes', 'face_rectangle', 'landmark']));
        return $face;
    }

    protected function mapFaces($data): array
    {
        $faces = [];
        foreach ((array)$data->{'faces'} as $face) {
            $faces[] = $this->mapFace($face);
        }
        return $faces;
    }

    protected function handleErrors(Data $data): void
    {
        if (isset($data->{'error_message'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'error_message'} ?? 'Something went wrong!');
        }
    }
}