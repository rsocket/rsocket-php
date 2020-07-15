<?php


namespace RSocket\routing;


class RSocketServiceRouter
{
    private static array $routingTable = [];

    public static function addService(string $serviceName, $serviceInstance): void
    {
        self::$routingTable[$serviceName] = $serviceInstance;
    }

    public static function isServiceAvailable(string $serviceName): bool
    {
        return array_key_exists($serviceName, self::$routingTable);
    }

    public static function invoke(string $serviceName, string $method, $params = null)
    {
        $callable = [self::$routingTable[$serviceName], $method];
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
