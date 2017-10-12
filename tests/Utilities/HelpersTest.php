<?php

namespace KaramanisWeb\FaceRD\Tests\Utilities;

use KaramanisWeb\FaceRD\Utilities\Helpers;
use KaramanisWeb\FaceRD\Utilities\InputEnum;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_get_input_type_returns_token()
    {
        $this->assertEquals(InputEnum::TOKEN, Helpers::getInputType('123demoTesting!'));
    }

    public function test_get_input_type_returns_url()
    {
        $this->assertEquals(InputEnum::URL, Helpers::getInputType('http://www.website.com/image.png'));
    }

    public function test_get_input_type_returns_base64()
    {
        $this->assertEquals(InputEnum::BASE64,
            Helpers::getInputType('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
    }

    public function test_get_input_type_returns_file()
    {
        $this->assertEquals(InputEnum::FILE, Helpers::getInputType(fopen(__DIR__ . '/../files/image.jpg', 'r')));
    }

    public function test_pulls_key_value_from_array_and_returns_it()
    {
        $array = [
            'name' => 'User',
            'age' => 23,
            'email' => 'user@email.com'
        ];
        $expected = [
            'age' => 23,
            'email' => 'user@email.com'
        ];

        $name = Helpers::arrayPull($array, 'name');
        $this->assertEquals('User', $name);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertCount(2, $array);
        $this->assertEquals($expected, $array);
    }

    public function test_removes_specified_keys_from_array_and_returns_array()
    {
        $array = [
            'name' => 'User',
            'age' => 23,
            'email' => 'user@email.com'
        ];
        $expected = [
            'email' => 'user@email.com'
        ];

        $arrayReturned = Helpers::arrayExcept($array, ['name', 'age']);
        $this->assertArrayNotHasKey('name', $arrayReturned);
        $this->assertArrayNotHasKey('age', $arrayReturned);
        $this->assertCount(1, $arrayReturned);
        $this->assertEquals($expected, $arrayReturned);
    }

    public function test_converts_array_into_string_and_returns_it()
    {
        $array = ['one', 'two', 'three'];
        $expected = 'one/two/three';

        $string = Helpers::arrayString($array, '/');
        $this->assertEquals($expected, $string);
    }

    public function test_converts_string_or_array_into_array_and_returns_it()
    {
        $string = 'word';
        $array = ['word'];
        $expected = ['word'];

        $array1 = Helpers::arrayWrap($string);
        $array2 = Helpers::arrayWrap($array);
        $this->assertEquals($expected, $array1);
        $this->assertEquals($expected, $array2);
    }

    public function test_if_keys_exists_into_an_array_returns_bool()
    {
        $array = [
            'name' => 'User',
            'age' => 23,
            'email' => 'user@email.com'
        ];

        $boolTrue = Helpers::arrayKeysExists($array, ['name', 'age', 'email']);
        $boolFalse = Helpers::arrayKeysExists($array, ['name', 'age', 'demo']);

        $this->assertTrue($boolTrue);
        $this->assertFalse($boolFalse);
    }

    public function test_explodes_a_string_into_an_array_based_on_separator_and_returns_it()
    {
        $string = 'one, two, three';
        $expected = ['one', 'two', 'three'];

        $array = Helpers::stringArray($string, ', ');

        $this->assertEquals($expected, $array);
        $this->assertCount(3, $array);
    }
}
