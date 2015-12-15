# Laravel JSON API Framework

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-jsonapi.svg?branch=master)](https://travis-ci.org/czim/laravel-jsonapi)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/laravel-jsonapi.svg)](https://packagist.org/packages/czim/laravel-jsonapi)

Framework for quick and easy setup of a JSON-API compliant server.


## Install

Via Composer

``` bash
$ composer require czim/laravel-jsonapi
```

- publish configuration
- set up kernel for middleware
    - add middleware to routes
- add trait to controllers
- extend package request for formrequests
- set up errorhandler for 'json' content
- optionally set validation error messages for:
    jsonapi_errors
    jsonapi_links
    jsonapi_resource
    jsonapi_relationships
    jsonapi_links
    jsonapi_link
    jsonapi_jsonapi
- add resource interface & (appliccable) trait to resource models 
- config:
    - add relationship hide & always_show_data per resource fqn

## To Do

# json api error responses
- consider using resource patcher
    - or some neat alternative to dealing with updates

- separate client json-api package...
    - use czim/service?
    - decoder/interpreter for json-api content received


## Usage

### Setting Meta data

Meta data is prepared for the next encoding by storing data in a dataobject singleton.
You can access it as follows:

```php
$meta = App::make(\Czim\JsonApi\Contracts\JsonApiCurrentMetaInterface::class);

$meta['some-key'] = 'some value';
```

The data object is an instance of a `DataObject` ([`czim/laravel-dataobject`](https://github.com/czim/laravel-dataobject)) instance.

By default, after calling the `encode()` (or `response()`) method on the Encoder, the meta-data will be reset.
This means that the lifetime of set meta-data ordinarily lasts until a response is fired.   


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Coen Zimmerman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-jsonapi.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-jsonapi.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-jsonapi
[link-downloads]: https://packagist.org/packages/czim/laravel-jsonapi
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
