<?php

namespace Drupal\wmcontroller_redis;

use Drupal\wmcontroller\Entity\Cache;
use Drupal\wmcontroller\Service\Cache\CacheSerializerInterface;

class Serializer implements CacheSerializerInterface
{
    /** @var \Drupal\wmcontroller\Service\Cache\CacheSerializerInterface */
    protected $serializer;

    public function __construct(CacheSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalize(Cache $cache, $includeContent = true)
    {
        return json_encode(
            $this->serializer->normalize($cache, $includeContent)
        );
    }

    public function denormalize($row)
    {
        return $this->serializer->denormalize(json_decode($row, true));
    }
}