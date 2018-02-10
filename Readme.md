# Adapter for Loco

[![Latest Version](https://img.shields.io/github/release/php-translation/loco-adapter.svg?style=flat-square)](https://github.com/php-translation/loco-adapter/releases)
[![Build Status](https://img.shields.io/travis/php-translation/loco-adapter.svg?style=flat-square)](https://travis-ci.org/php-translation/loco-adapter)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/loco-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/loco-adapter)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/loco-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/loco-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/loco-adapter.svg?style=flat-square)](https://packagist.org/packages/php-translation/loco-adapter)

This is an PHP-translation adapter for Loco ([Localise.biz](https://localise.biz/)). 

### Install

```bash
composer require php-translation/loco-adapter
```

##### Symfony bundle

If you want to use the Symfony bundle you may activate it in kernel:
```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Translation\PlatformAdapter\Loco\Bridge\Symfony\TranslationAdapterLocoBundle(),
    );
}
```

If you have one Loco project per domain you may configure the bundle like this: 
```yaml
# /app/config/config.yml
translation_adapter_loco:
  index_parameter: 'id' # 'text' or 'name'. Leave blank for "auto"  See https://localise.biz/api/docs/export/exportlocale
  projects:
    messages:
      api_key: 'foobar' 
    navigation:
      api_key: 'bazbar' 
```

If you just doing one project and have tags for all your translation domains you may use this configuration:
```yaml
# /app/config/config.yml
translation_adapter_loco:
  index_parameter: 'id' # 'text' or 'name'. Leave blank for "auto"  See https://localise.biz/api/docs/export/exportlocale
  projects:
    acme:
      api_key: 'foobar'   
      domains: ['messages', 'navigation']
```

This will produce a service named `php_translation.adapter.loco` that could be used in the configuration for
the [Translation Bundle](https://github.com/php-translation/symfony-bundle).

### Documentation

Read our documentation at [http://php-translation.readthedocs.io](http://php-translation.readthedocs.io/en/latest/).

### Contribute

Do you want to make a change? Pull requests are welcome.
