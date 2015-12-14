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


## To Do

- good encoder basis
- good schema provider basis
- generator command to create schemata based on models
- json api error responses
- decoder/interpreter for json-api content received
    - separate to be used in client package aswell 


- separate client json-api package...
    - use czim/service?
    

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
