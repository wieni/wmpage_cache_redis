<?php

namespace Drupal\wmpage_cache_redis;

/**
 * We extend it because we want a different client
 * The ClientFactory calls static::$client so we cannot re-use the same
 * factory. This sucks.
 */
class RedisCacheTagsChecksumClientFactory extends RedisClientFactory
{
    protected static $client;
}
