<?php

namespace Drupal\wmpage_cache_redis;

use Drupal\Core\Site\Settings;
use Drupal\redis\Client\PhpRedis;
use Drupal\redis\ClientFactory;

class RedisClientFactory extends ClientFactory
{
    /** @var PhpRedis */
    protected static $client;

    public static function getClient(): ?PhpRedis
    {
        if (isset(static::$client)) {
            return static::$client;
        }

        $settings = Settings::get('wmcontroller.redis.connection', []);
        $settings += [
            'host' => ClientFactory::REDIS_DEFAULT_HOST,
            'port' => ClientFactory::REDIS_DEFAULT_PORT,
            'base' => ClientFactory::REDIS_DEFAULT_BASE,
            'password' => ClientFactory::REDIS_DEFAULT_PASSWORD,
        ];

        try {
            return static::$client = (new PhpRedis())->getClient(
                $settings['host'],
                $settings['port'],
                $settings['base'],
                $settings['password']
            );
        } catch (\Exception $e) {
            watchdog_exception('wmcontroller.redis', $e);
            return null;
        }
    }
}
