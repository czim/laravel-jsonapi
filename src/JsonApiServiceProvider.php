<?php
namespace Czim\JsonApi;

use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Contracts\SchemaProviderInterface;
use Czim\JsonApi\Encoding\NullSchemaProvider;
use Czim\JsonApi\Parameters\JsonApiParameters;
use Illuminate\Support\ServiceProvider;

class JsonApiServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/jsonapi.php' => config_path('jsonapi.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/jsonapi.php', 'jsonapi'
        );

        // bindings for middleware / json-api global state for includes etc
        $this->app->singleton(JsonApiParametersInterface::class, JsonApiParameters::class);

        $this->app->bind(SchemaProviderInterface::class, NullSchemaProvider::class);
    }

}
