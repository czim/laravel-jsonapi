<?php
namespace Czim\JsonApi\Encoding;

use Czim\JsonApi\Contracts\SchemaProviderInterface;
use Illuminate\Contracts\Container\Container;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Schema\Link;

class JsonApiEncoder
{

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var SchemaProviderInterface
     */
    protected $schemaProvider;


    /**
     * JsonApiEncoder constructor.
     *
     * @param Container               $app
     * @param SchemaProviderInterface $schemaProvider
     */
    public function __construct(Container $app, SchemaProviderInterface $schemaProvider)
    {
        $this->app            = $app;
        $this->schemaProvider = $schemaProvider;
    }


    /**
     * Encodes data as valid JSON API response and returns it
     *
     * @param mixed $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response($data)
    {
        return response( $this->encode($data) )
            ->setTtl( config('jsonapi.default_ttl', 60) );
    }

    /**
     * Encodes data as valid JSON API output
     *
     * @param mixed $data
     * @return $this
     */
    public function encode($data)
    {
        // todo based on what data is provided,
        // the encoding should be handled..
        // we're expecting something that implements the resourceinterface

        // todo handle meta properly


        return $this->getEncoder()
            ->withLinks([
                Link::SELF => new Link( $this->getUrlToSelf() ),
            ])
            //->withMeta( (EloquentSchema::$meta ?: null) )
            ->encodeData($data);
    }

    /**
     * @return EncoderInterface
     */
    protected function getEncoder()
    {
        return Encoder::instance(
            $this->getEncoderSchemaMapping(),
            $this->getEncoderOptions()
        );
    }

    /**
     * returns the SchemaProvider/mapping
     *
     * @return array
     */
    protected function getEncoderSchemaMapping()
    {
        return $this->schemaProvider->getSchemaMapping();
    }

    /**
     * Returns Encoder options to inject into the Encoder
     *
     * @return EncoderOptions
     */
    protected function getEncoderOptions()
    {
        return new EncoderOptions(
            config('jsonapi.encoding.encoder_options', JSON_UNESCAPED_SLASHES),
            $this->getUrlToRoot()
        );
    }


    /**
     * Returns relative URL to the encoded content's (current request) self
     *
     * @return string
     */
    protected function getUrlToSelf()
    {
        return join('/', array_slice($this->app->make('request')->segments(), 1));
    }

    /**
     * Returns URL to 'root' of API
     *
     * @return string
     */
    protected function getUrlToRoot()
    {
        $baseUrl = config('jsonapi.base_url');
        $basePath = '/' . ltrim(config('jsonapi.base_url_path'), '/');

        if ( ! empty($baseUrl)) return $baseUrl . $basePath;

        return $this->app->make('request')->root() . $basePath;
    }


}
