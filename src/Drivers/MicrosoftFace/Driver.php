<?php

namespace KaramanisWeb\FaceRD\Drivers\MicrosoftFace;

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
    protected $driver = 'MicrosoftFace';
    protected $apiBase = 'https://westcentralus.api.cognitive.microsoft.com/face/v1.0';
    protected $requiredCredentials = ['Ocp-Apim-Subscription-Key'];
    protected $headerAuth = true;

    public function detect($input, array $options = []): array
    {
        if (Helpers::getInputType($input) !== InputEnum::URL) {
            throw new notSupported('Only image url is supported as input.');
        }

        $resource = 'detect/';
        $resource .= '?returnFaceAttributes=' . str_replace(' ', '', $options['attributes'] ?? '');
        $resource .= '&returnFaceLandmarks=' . var_export($options['landmark'] ?? false, true);
        $this->request->setResource($resource);
        $this->request->sent('POST:json', [
            'url' => $input
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapFaces($data->toArray());
    }

    public function compare($input1, $input2, array $options = []): Result
    {
        if (Helpers::getInputType($input1) !== InputEnum::TOKEN || Helpers::getInputType($input2) !== InputEnum::TOKEN) {
            throw new notSupported('Only face tokens are supported for comparison.');
        }

        $this->request->setResource('verify');
        $this->request->sent('POST:json', [
            'faceId1' => $input1,
            'faceId2' => $input2
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapCompare($data->toArray());
    }

    public function recognise($input, string $group, array $options = []): Result
    {
        if (Helpers::getInputType($input) !== InputEnum::TOKEN) {
            throw new notSupported('Only one face token is supported for face recognition.');
        }

        $this->request->setResource('findsimilars');
        $this->request->sent('POST:json', [
            'faceListId' => $group,
            'faceId' => $input
        ]);
        $data = $this->request->getData();

        $this->handleErrors($data);
        return $this->mapRecognise($data->toArray());
    }

    protected function mapRecognise($data): Result
    {
        $data = Helpers::arrayExcept($data, ['statusCode', 'unmapped']);
        $result = new Result($this->driver, uniqid('', true));
        $result->setMatches($data);
        return $result;
    }

    protected function mapCompare($data): Result
    {
        $result = new Result($this->driver, uniqid('', true));
        $result->setConfidence($data['confidence']);
        return $result;
    }

    protected function mapFaces($data): array
    {
        $faces = [];
        $data = Helpers::arrayExcept($data, ['statusCode', 'unmapped']);
        foreach ($data as $face) {
            $faces[] = $this->mapFace($face);
        }
        return $faces;
    }

    protected function mapFace($data): Face
    {
        $face = new Face($this->driver, $data['faceId']);
        $face->setAttributes($data['faceAttributes'] ?? []);
        $face->setLandmark($data['faceLandmarks'] ?? []);
        $rectangle = $data['faceRectangle'];
        $face->setRectangle($rectangle['left'], $rectangle['top'], $rectangle['width'], $rectangle['height']);
        $face->setUnmapped(Helpers::arrayExcept($data, ['faceId', 'faceAttributes', 'faceLandmarks', 'faceRectangle']));
        return $face;
    }

    protected function handleErrors(Data $data): void
    {
        if (isset($data->{'error'}) || $this->failedDataStatus($data)) {
            throw new failedRequest($data->{'error'}['message'] ?? 'Something went wrong!');
        }
    }
}