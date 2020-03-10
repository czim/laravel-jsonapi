<?php
namespace Czim\JsonApi\Providers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerFactoryInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Contracts\Support\Request\RequestQueryParserInterface;
use Czim\JsonApi\Contracts\Support\Resource\ResourcePathHelperInterface;
use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Czim\JsonApi\Contracts\Support\Validation\JsonApiValidatorInterface;
use Czim\JsonApi\Encoder\Encoder;
use Czim\JsonApi\Encoder\Factories\TransformerFactory;
use Czim\JsonApi\Facades;
use Czim\JsonApi\Repositories\ResourceCollector;
use Czim\JsonApi\Repositories\ResourceRepository;
use Czim\JsonApi\Support\Request\RequestQueryParser;
use Czim\JsonApi\Support\Resource\ResourcePathHelper;
use Czim\JsonApi\Support\Type\TypeMaker;
use Czim\JsonApi\Support\Validation\JsonApiValidator;
use Illuminate\Support\ServiceProvider;

class JsonApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootConfig();
    }

    public function register(): void
    {
        $this->registerConfig();
        $this->registerInterfaces();
        $this->loadAliases();
    }


    protected function registerInterfaces(): void
    {
        $this->app->singleton(RequestQueryParserInterface::class, RequestQueryParser::class);
        $this->app->singleton(JsonApiValidatorInterface::class, JsonApiValidator::class);
        $this->app->singleton(TypeMakerInterface::class, TypeMaker::class);
        $this->app->singleton(ResourceRepositoryInterface::class, ResourceRepository::class);
        $this->app->singleton(ResourceCollectorInterface::class, ResourceCollector::class);
        $this->app->singleton(EncoderInterface::class, Encoder::class);
        $this->app->singleton(TransformerFactoryInterface::class, TransformerFactory::class);
        $this->app->singleton(ResourcePathHelperInterface::class, ResourcePathHelper::class);
    }

    protected function loadAliases(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();

        $loader->alias('JsonApiRequest', Facades\JsonApiRequestFacade::class);
        $loader->alias('JsonApiEncoder', Facades\JsonApiEncoderFacade::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/jsonapi.php', 'jsonapi');
    }

    protected function bootConfig(): void
    {
        $this->publishes(
            [
                realpath(__DIR__ . '/../../config/jsonapi.php') => config_path('jsonapi.php'),
            ],
            'jsonapi'
        );
    }
}
