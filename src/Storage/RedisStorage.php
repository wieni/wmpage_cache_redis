<?php

namespace Drupal\wmcontroller_redis\Storage;

use Drupal\wmcontroller\Entity\Cache;
use Drupal\wmcontroller\Exception\NoSuchCacheEntryException;
use Drupal\wmcontroller\Service\Cache\CacheSerializerInterface;
use Drupal\wmcontroller\Service\Cache\Storage\StorageInterface;
use Drupal\wmcontroller_redis\RedisClientFactory;
use Redis;

class RedisStorage implements StorageInterface
{
    /** @var \Redis */
    protected $redis;
    /** @var \Drupal\wmcontroller\Service\Cache\CacheSerializerInterface */
    protected $serializer;
    protected $prefix;

    public function __construct(
        RedisClientFactory $clientFactory,
        CacheSerializerInterface $serializer,
        $prefix = ''
    ) {
        try {
            $this->redis = $clientFactory::getClient();
            if (!$this->redis instanceof Redis) {
                throw new \RuntimeException(
                    'RedisClientFactory did not return a Redis instance. Silently failing...'
                );
            }
        } catch (\Exception $e) {
            $this->redis = null;
            watchdog_exception('wmcontroller.redis', $e);
        }
        $this->serializer = $serializer;
        $this->prefix = $prefix;
    }

    public function load($id, $includeBody = true)
    {
        $item = $this->loadMultiple([$id], $includeBody)->current();
        if (!$item) {
            throw new NoSuchCacheEntryException($id);
        }

        return $item;
    }

    public function loadMultiple(array $ids, $includeBody = true): \Iterator
    {
        if (!$this->redis) {
            return [];
        }

        $time = time();

        foreach (array_chunk($ids, 50) as $chunk) {
            if ($includeBody) {
                $rows = $this->redis->mget($this->prefix($chunk, 'body'));
            } else {
                $rows = $this->redis->mget($this->prefix($chunk));
            }

            foreach (array_filter($rows) as $row) {
                $item = $this->serializer->denormalize($row);
                if ($item->getExpiry() > $time) {
                    yield $item;
                }
            }
        }
    }

    public function set(Cache $item, array $tags)
    {
        if (!$this->redis) {
            return;
        }
        $id = $item->getId();
        $time = time();

        $tx = $this->redis->multi();

        $tx->set(
            $this->prefix($id),
            $this->serializer->normalize($item, false),
            ($item->getExpiry() - $time)
        );
        $tx->set(
            $this->prefix($id, 'body'),
            $this->serializer->normalize($item, true),
            ($item->getExpiry() - $time)
        );
        $tx->zAdd(
            $this->prefix('expiries'),
            $item->getExpiry(),
            $id
        );
        $tx->del($this->prefix($id, 'tags'));
        $tx->sAdd(
            $this->prefix($id, 'tags'),
            ...$tags
        );
        foreach ($tags as $tag) {
            $tx->sAdd(
                $this->prefix($tag),
                $id
            );
        }
        $path = parse_url($item->getUri(), PHP_URL_PATH);
        if ($path) {
            $tx->set(
                $this->prefix($id, 'path'),
                $path
            );
        }

        $tx->exec();
    }

    public function getByTags(array $tags)
    {
        if (!$this->redis || !$tags) {
            return [];
        }

        $tags = array_map([$this, 'prefix'], $tags);

        $this->redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);

        $ids = [];
        foreach ($tags as $tag) {
            $iterator = null;
            while ($results = $this->redis->sScan($tag, $iterator)) {
                foreach ($results as $id) {
                    $ids[$id] = true;
                }
            }
        }

        return array_keys($ids);
    }

    public function remove(array $ids)
    {
        if (!$this->redis) {
            return;
        }

        foreach ($ids as $id) {
            $tags = $this->redis->sMembers($this->prefix($id, 'tags'));

            $tx = $this->redis->multi();

            $tx->del($this->prefix($id));
            $tx->del($this->prefix($id, 'body'));
            $tx->del($this->prefix($id, 'tags'));
            $tx->del($this->prefix($id, 'path'));
            $tx->zRem($this->prefix('expiries'), $id);

            foreach ($tags as $tag) {
                $tx->sRem(
                    $this->prefix($tag),
                    $id
                );
            }

            $tx->exec();
        }
    }

    public function getExpired($amount)
    {
        if (!$this->redis) {
            return [];
        }
        $ids = $this->redis->zRangeByScore(
            $this->prefix('expiries'),
            1,
            time(),
            [
                'limit' => [0, $amount],
            ]
        );

        return $ids;
    }

    public function flush()
    {
        if (!$this->redis) {
            return;
        }

        // Todo: isn't this going to go OOM?
        $this->redis->del(
            $this->redis->keys(
                $this->prefix('*')
            )
        );
    }

    private function prefix($string, $prefix = '')
    {
        if (is_array($string)) {
            $result = [];
            foreach ($string as $str) {
                $result[] = $this->prefix($str, $prefix);
            }
            return $result;
        }
        return $this->prefix . ($prefix ? "$prefix:" : '') . $string;
    }
}
