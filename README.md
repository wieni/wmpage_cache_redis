wmpage_cache_redis
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmpage_cache_redis/v/stable)](https://packagist.org/packages/wieni/wmpage_cache_redis)
[![Total Downloads](https://poser.pugx.org/wieni/wmpage_cache_redis/downloads)](https://packagist.org/packages/wieni/wmpage_cache_redis)
[![License](https://poser.pugx.org/wieni/wmpage_cache_redis/license)](https://packagist.org/packages/wieni/wmpage_cache_redis)

> A Redis cache storage for [wieni/wmpage_cache](https://github.com/wieni/wmpage_cache)

## Installation

This package requires PHP 7.1, PhpRedis and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmpage_cache_redis
```

To enable this cache storage, change the following container parameters:
```yaml
parameters:
    wmpage_cache.storage: wmpage_cache.storage.redis
    wmpage_cache.invalidator: wmpage_cache_redis.checksum
    wmpage_cache.checksum: wmpage_cache_redis.checksum

    wmpage_cache.redis.prefix: 'wmpage_cache:'
```

Make sure to also set the host & base in `settings.php`.
```php
$settings['wmpage_cache.redis.connection']['host'] = '127.0.0.1';
$settings['wmpage_cache.redis.connection']['base'] = '0';
```

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
