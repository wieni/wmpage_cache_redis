<?php

namespace Drupal\wmpage_cache_redis;

use Drupal\wmpage_cache\Cache;
use Drupal\wmpage_cache\CacheSerializerInterface;

class Serializer implements CacheSerializerInterface
{
    /** @var CacheSerializerInterface */
    protected $serializer;

    public function __construct(CacheSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalize(Cache $cache, $includeContent = true): ?string
    {
        return json_encode(
            $this->serializer->normalize($cache, $includeContent)
        ) ?: null;
    }

    public function denormalize($row): Cache
    {
        return $this->serializer->denormalize(json_decode($row, true));
    }
}
