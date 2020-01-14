<?php

declare(strict_types=1);

namespace Keboola\SnowflakeGrantAPI;

class Utils
{
    public static function getEnv(string $name): string
    {
        if (empty(getenv($name))) {
            throw new \Exception(sprintf('Env variable "%s" must not be empty', $name));
        }
        return (string) getenv($name);
    }
}
