<?php

namespace KaramanisWeb\FaceRD\Contracts;

interface ManagerInterface
{
    public function configure(string $driver, array $credentials);

    public function base(): DriverInterface;

    public function group(): GroupInterface;
}