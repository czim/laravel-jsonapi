<?php
namespace Czim\JsonApi;

use Czim\JsonApi\Contracts\JsonApiCurrentMetaInterface;
use Czim\JsonApi\Contracts\JsonApiEncoderInterface;
use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Contracts\SchemaProviderInterface;
use Czim\JsonApi\DataObjects\Meta;
use Czim\JsonApi\Encoding\JsonApiEncoder;
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

        // bindings for middleware / JSON-API global state for includes etc
        $this->app->singleton(JsonApiParametersInterface::class, JsonApiParameters::class);
        $this->app->singleton(JsonApiCurrentMetaInterface::class, Meta::class);

        $this->app->bind(SchemaProviderInterface::class, NullSchemaProvider::class);
        $this->app->bind(JsonApiEncoderInterface::class, JsonApiEncoder::class);

        // binding for Encoder Facade
        $this->app->singleton('jsonapi.encoder', JsonApiEncoderInterface::class);

    }

}
