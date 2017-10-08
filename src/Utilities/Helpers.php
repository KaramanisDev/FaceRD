<?php

namespace KaramanisWeb\FaceRD\Utilities;

class Helpers
{
    public static function getDriver($object): string
    {
        return explode('\\', get_class($object))[3];
    }

    public static function getInputType($input): string
    {
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return InputEnum::URL;
        }

        if (is_resource($input)) {
            return InputEnum::FILE;
        }

        try {
            if (imagecreatefromstring(base64_decode($input, true))) {
                return InputEnum::BASE64;
            }
        } catch (\Exception $e) {
        }

        return InputEnum::TOKEN;
    }

    public static function arrayPull(array &$value, string $variable): mixed
    {
        if (isset($value[$variable])) {
            $temp = $value[$variable];
            unset($value[$variable]);
            return $temp;
        }
        return null;
    }

    public static function arrayExcept(array $value, array $except): array
    {
        foreach ($except as $key) {
            unset($value[$key]);
        }
        return $value;
    }

    public static function arrayString($value, string $separator = ','): string
    {
        return implode($separator, self::arrayWrap($value));
    }

    public static function arrayWrap($value): array
    {
        return !is_array($value) ? [$value] : (array)$value;
    }

    public static function arrayKeysExists(array $value, array $keys): bool
    {
        return !array_diff_key(array_flip($keys), $value);
    }

    public static function stringArray(string $value, string $separator = ','): array
    {
        return explode($separator, $value);
    }
}