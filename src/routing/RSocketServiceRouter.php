<?php


namespace RSocket\routing;


use Rx\Observable;

class RSocketServiceRouter
{
    private array $routingTable = [];

    public function addService(string $serviceName, $serviceInstance): void
    {
        $this->routingTable[$serviceName] = $serviceInstance;
    }

    public function isServiceAvailable(string $serviceName): bool
    {
        return array_key_exists($serviceName, $this->routingTable);
    }

    public function invoke(string $serviceName, string $method, $params = null): Observable
    {
        $callable = [$this->routingTable[$serviceName], $method];
        if ($params === null) {
            return $callable();
        }
        if (is_array($params)) {
            if (count($params) === 0) {
                return $callable();
            }
            return $callable(...$params);
        }
        return $callable($params);
    }
}
