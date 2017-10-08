<?php

namespace KaramanisWeb\FaceRD;

use KaramanisWeb\FaceRD\Contracts\DriverInterface;
use KaramanisWeb\FaceRD\Contracts\GroupInterface;
use KaramanisWeb\FaceRD\Exceptions\driverNotFound;
use KaramanisWeb\FaceRD\Exceptions\notSupported;

class FaceRDManager
{
    protected $driver;

    public function __construct(string $driver, array $credentials)
    {
        $this->setDriver($driver, $credentials);
    }

    public function configure(string $driver, array $credentials): void
    {
        $this->driver = $this->findDriver($driver, $credentials);
    }

    public function base(): DriverInterface
    {
        return $this->getDriver();
    }

    public function group(): GroupInterface
    {
        return $this->getDriver()->group();
    }

    protected function findDriver(string $driver, array $credentials): DriverInterface
    {
        $driverClass = sprintf('KaramanisWeb\\FaceRD\\Drivers\\%s\\Driver', $driver);
        if (!class_exists($driverClass)) {
            throw new driverNotFound($driver . ' driver does not exist.');
        }
        return new $driverClass($credentials);
    }

    protected function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    protected function setDriver(string $driver, array $credentials): void
    {
        $this->driver = $this->findDriver($driver, $credentials);
    }

    public function __call($method, $parameters)
    {
        if(!is_callable([$this->getDriver(),$method])){
            throw new notSupported();
        }
        return $this->getDriver()->$method(...$parameters);
    }
}