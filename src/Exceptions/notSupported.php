<?php

namespace KaramanisWeb\FaceRD\Exceptions;

class notSupported extends \Exception
{
    protected $message = 'This feature is not support by this driver.';
}