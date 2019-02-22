# Redis storage for WmController

This is a Redis storage for [wieni/wmcontroller](https://github.com/wieni/wmcontroller)

## Installation

It currently only supports `PhpRedis`.

```bash
composer require wieni/wmcontroller_redis
drush en wmcontroller_redis
```

```yaml
// services.yml
parameters:
    wmcontroller.cache.storage: wmcontroller.cache.storage.redis

    wmcontroller.cache.redis.prefix: 'wmcontroller:'
```

```php
// settings.php
$settings['wmcontroller.redis.connection']['host'] = '127.0.0.1';
$settings['wmcontroller.redis.connection']['base'] = '0';
```
