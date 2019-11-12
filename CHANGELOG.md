# Changelog

### [1.5.3] - 2019-11-12

Now treats empty `attributes` object in request as valid.

### [1.5.2] - 2019-03-06

Now dependent on czim/laravel-dataobject 2.0+.
Requires PHP 7.1.3+.

### [1.5.1] - 2019-03-02

Removed deprecated use of Laravel array and string helper functions.

### [1.5.0] - 2019-03-02

Laravel 5.7 support.

### [1.4.16] - 2019-01-08

Added configurable validation rules for the query string for a request.  
This prevents incorrect filter, include, sort and pagination parameters.
Adds the `jsonapi.request.validaton` configuration section. 

[1.5.3]: https://github.com/czim/laravel-jsonapi/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/czim/laravel-jsonapi/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/czim/laravel-jsonapi/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/czim/laravel-jsonapi/compare/1.4.16...1.5.0

[1.4.16]: https://github.com/czim/laravel-jsonapi/compare/1.4.15...1.4.16
