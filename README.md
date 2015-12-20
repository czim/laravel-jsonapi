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

If you have any problems with 'dev-master' rights, add the following package requirements:

``` bash
$ composer require znck/belongs-to-through
$ composer require neomerx/json-api
```

Add this line of code to the `providers` array located in your `config/app.php` file:

``` php
    Czim\JsonApi\JsonApiServiceProvider::class,
```

And add this line of code to the `aliases` array in `config/app.php` for the Facade:

``` php
    'Encoder' => Czim\JsonApi\Facades\Encoder::class,
```

Publish the configuration:

``` bash
$ php artisan vendor:publish
```


## Set up

### Set up middleware

In `app/Http/Kernel.php`, add the following to the `$routeMiddleware` property.

``` php
    'jsonapi.headers'    => \Czim\JsonApi\Middleware\JsonApiHeaders::class,
    'jsonapi.parameters' => \Czim\JsonApi\Middleware\JsonApiParametersSetup::class,
```

Then set up the middleware for your routes in `app/routes.php`, for instance as follows:
 
``` php
    Route::group(
        [
            'prefix'     => 'v1',
            'namespace'  => 'v1',
            'middleware' => [
                'jsonapi.headers',
                'jsonapi.parameters',
            ],
        ],
        function () {
 
            // Your API routes...
        }
    );
```

Additionally, do not forget to remove or alternatively handle the `VerifyCsrfToken` middleware, if you intend to accept POST requests to your API.


### Set up the error handler

To make the error handler output errors in the correct format, you'll need to modify `app/Exceptions/Handler.php`.
When any routes using the JSON-API standard are hit, error response should be a list of errors formatted as JSON-API error objects. This may be done by setting up the `render` method as follows: 

``` php
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }
        
        // For json, encode errors according to JSON-API formatting 
        if ($request->isJson()) {
            return \Encoder::errors($e);
        }

        return parent::render($request, $e);
    }
```

### Set up validation messages

Optionally you can set up validation message translations for JSON-API structure errors.
To do so, add the following to, for instance, `resources/lang/en/validation.php`: 

``` php
    'jsonapi_errors'        => 'The JSON-API errors list is malformed.',
    'jsonapi_resource'      => 'The JSON-API resource is malformed.',
    'jsonapi_links'         => 'The JSON-API links list is malformed.',
    'jsonapi_link'          => 'The JSON-API link object is malformed.',
    'jsonapi_relationships' => 'The JSON-API relationships list is malformed.',
    'jsonapi_jsonapi'       => 'The JSON-API json-api object is malformed.',
```

### Define relationships in the config

- hide relationships
- always_show_data


### Set up models and other resource objects

- add the resource interface
- add the appropriate traits (or roll your own)


## Usage

### Requests and reading JSON-API Request Data 

To validate a `POST` or `PUT` request with JSON-API content, use Laravel's service container to instantiate an instance of `Czim\JsonApi\Requests\JsonApiRequest`.
 
In a controller:

``` php
use Czim\JsonApi\Requests\JsonApiRequest;

class TestController extends Controller
{
    public function someAction(JsonApiRequest $request)
    {
        // ...
```

Or anywhere outside of it:

``` php
$request = App::make(\Czim\JsonApi\Requests\JsonApiRequest::class);
```

This will perform validation on the general JSON-API structure and throw the normal request 422 validation errors, formatted as JSON-API error list. Note that empty requests will pass validation, unless specific validation rules are set up in classes extending the `JsonApiRequest` class (for requiring content, see the section on specific validation below).


#### Accessing JSON-API Request Data

The instantiated request has accessor methods to make it easier to extract typical JSON-API data from the request.

``` php
    // resource data object (from single resource request or first from list) 
    $resource = $request->getResource();
    
    // list of relationships as an associative array of data objects
    $relationships = $request->getRelationships();
    
    // specific relationship data object
    $relationship = $request->getRelationship('categories');
    
    // attributes collection data object for first or single resource
    $attributes = $request->getAttributes();
    
    // value for a specific attribute by key
    $value = $request->getAttribute('some_attribute');
    
    // nested value for a specific attribute by key in dot notation
    $value = $request->getAttribute('some_array.some_attribute');
```

For more options and information, see [`JsonApiDataAccessorsInterface.php`](https://github.com/czim/laravel-jsonapi/blob/master/src/Contracts/JsonApiDataAccessorsInterface.php).


#### Validating specific JSON-API content
 
To set up normal validation, extend the `JsonApiRequest` as you would for a normal FormRequest.

Data validation for specific requests may then be handled normally.
For instance, for a request that requires some attributes and a relationship be provided:

``` php
    public function rules()
    {
        return [
            // Attribute rules
            'data.attributes.from-date'            => 'required|date',
            'data.attributes.to-date'              => 'required|date',
            'data.attributes.description'          => 'string',
            // Relationship rules
            'data.relationships.address.data.type' => 'required|in:addresses',
            'data.relationships.address.data.id'   => 'required|exists:addresses,id',
        ];
    }
```


### Setting Meta data

Meta data is prepared for the next encoding by storing data in a dataobject singleton.
You can access it as follows:

``` php
// method 1: through the Encoder facade
$meta = \Encoder::getMeta();

// method 2: directly through the bound interface
$meta = App::make(\Czim\JsonApi\Contracts\JsonApiCurrentMetaInterface::class);

// method 3: through the JsonApiEncoder binding
$meta = app(\Czim\JsonApi\Encoding\JsonApiEncoderInterface::class)->getMeta();

$meta['some-key'] = 'some value';
```

The data object is an instance of a `DataObject` ([`czim/laravel-dataobject`](https://github.com/czim/laravel-dataobject)) instance.

By default, after calling the `encode()` (or `response()`) method on the Encoder, the meta-data will be reset.
This means that the lifetime of set meta-data ordinarily lasts until a response is fired.   


### Accessing Special Request Parameters

Special request parameters, such as included paths, filters and sorting options are automatically read when the `JsonApiParametersSetup` middleware runs. After that, the settings read can be accessed through a bound singleton, similar to that for the Meta data.

```php
// method 1: through the Encoder facade
$meta = \Encoder::getParameters();

// method 2: directly through the bound interface
$parameters = App::make(\Czim\JsonApi\Contracts\JsonApiParametersInterface::class);

// method 3: through the JsonApiEncoder binding
$parameters = app(\Czim\JsonApi\Encoding\JsonApiEncoderInterface::class)->getParameters();

// Get an array of paths to include, which take the dot notation
$parameters->getIncludePaths();

// Get all filter values as an array
$parameters->getFilter();

// Get a specific filter value by key
$parameters->getFilterValue('ids');

// Get an array with sort parameters in order
// These implement the `Czim\JsonApi\Contracts\SortParameterInterface`
$parameters->getSortParameters();
```

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
