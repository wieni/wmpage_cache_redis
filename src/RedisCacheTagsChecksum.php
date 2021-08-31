<?php

namespace Drupal\wmpage_cache_redis;

use Drupal\Core\Cache\CacheTagsChecksumInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\wmpage_cache\InvalidatorInterface;

class RedisCacheTagsChecksum implements CacheTagsChecksumInterface, CacheTagsInvalidatorInterface, InvalidatorInterface
{
    /** @var RedisClientFactory */
    protected $redisClientFactory;
    /** @var CacheTagsChecksumInterface|CacheTagsInvalidatorInterface|null */
    protected $redisCacheTagsChecksum;

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    public function getCurrentChecksum(array $tags)
    {
        if ($decorated = $this->decorated()) {
            return $decorated->getCurrentChecksum($tags);
        }

        return 'noop';
    }

    public function isValid($checksum, array $tags)
    {
        if ($decorated = $this->decorated()) {
            return $decorated->isValid($checksum, $tags);
        }

        return false;
    }

    public function reset()
    {
        if ($decorated = $this->decorated()) {
            $decorated->reset();
        }
    }

    public function invalidateCacheTags(array $tags): void
    {
        // noop
        // We use Drupal's cache tag invalidation system to invalidate the cache
    }

    public function invalidateTags(array $tags)
    {
        if ($decorated = $this->decorated()) {
            $decorated->invalidateTags($tags);
        }
    }

    /** @return CacheTagsChecksumInterface|CacheTagsInvalidatorInterface|null */
    protected function decorated()
    {
        if (isset($this->redisCacheTagsChecksum)) {
            return $this->redisCacheTagsChecksum ?: null;
        }

        $decorated = false;
        try {
            $client = $this->redisClientFactory::getClient();
            if ($client instanceof \Redis) {
                $decorated = new RedisCacheTagsChecksum($this->redisClientFactory);
            }
        } catch (\Exception $e) {
            watchdog_exception('wmcontroller.redis', $e);
        }

        return $this->redisCacheTagsChecksum = $decorated;
    }
}
