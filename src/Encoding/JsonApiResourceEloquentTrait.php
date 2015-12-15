<?php
namespace Czim\JsonApi\Encoding;

use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Contracts\ResourceStaticRelationsInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Neomerx\JsonApi\Schema\SchemaProvider;

/**
 * Use this to make Eloquent models implement the ResourceInterface
 */
trait JsonApiResourceEloquentTrait
{

    /**
     * Returns resource path
     *
     * @return string
     */
    public function getResourcePath()
    {
        return str_replace('_', '-', $this->getTable()) . '/';
    }

    /**
     * Returns resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return Str::snake(
            Str::plural(
                class_basename($this)
            ),
            '-'
        );
    }

    /**
     * Returns resource ID
     *
     * @return string
     */
    public function getResourceId()
    {
        return (string) $this->getKey();
    }

    /**
     * Returns attributes for the resource
     *
     * @return mixed[]
     */
    public function getResourceAttributes()
    {
        $attributes = $this->attributesToArray();

        // detect any translated attributes
        if (in_array(config('jsonapi.encoding.translatable_trait'), class_uses($this))) {

            foreach ($this->translatedAttributes as $key) {

                $attributes[ $key ] = $this->$key;
            }
        }

        // unset primary key field
        unset($attributes[ $this->getKeyName() ]);

        // unset reserved fieldname "type"
        // reset it as modelname-type if that is not in use yet
        if (array_key_exists('type', $attributes)) {
            $typeRecast = Str::snake(class_basename($this) . '-type', '-');

            if ( ! array_key_exists($typeRecast, $attributes)) {
                $attributes[ $typeRecast ] = $attributes['type'];
            }

            unset($attributes['type']);
        }


        // map all fieldnames to dasherized case
        foreach ($attributes as $key => $value) {

            $dasherized = str_replace('_', '-', $key);

            if ($key !== $dasherized) {
                $attributes[ $dasherized ] = $value;
                unset($attributes[ $key ]);
            }
        }

        return $attributes;
    }

    /**
     * Returns relations for the resource
     *
     * @param bool $showEmpty   whether to include empty relationships
     * @return mixed[]
     */
    public function getResourceRelations($showEmpty = false)
    {
        $relations = [];

        /** @var JsonApiParametersInterface $jsonApiParameters */
        $jsonApiParameters = App::make(JsonApiParametersInterface::class);
        $requestedIncludes = $jsonApiParameters->getIncludePaths();

        $configuredToHide     = config('jsonapi.relations.hide.' . get_class($this), []);
        $configuredToShowData = config('jsonapi.relations.always_show_data.' . get_class($this), []);

        foreach ($this->getRelations() as $relationName => $related) {

            if (    ( ! $showEmpty && empty($related))
                ||  in_array($relationName, config('jsonapi.relations.hide_defaults', []))
                ||  in_array($relationName , $configuredToHide)
            ) {
                continue;
            }

            // show data either when includes are requested,
            // or the relationship is of a to-one type
            // or we've configured the relationship to always show its data

            if (    in_array($relationName, $requestedIncludes)
                ||  in_array($relationName, $configuredToShowData)
            ) {
                $showData = true;

            } else {

                $relation = $this->{$relationName}();

                $showData = JsonApiEncoder::alwaysIncludeDataForRelation($relation);
            }


            $relations[ Str::snake($relationName, '-') ] = [
                SchemaProvider::SHOW_DATA    => $showData,
                SchemaProvider::DATA         => $related,
                SchemaProvider::SHOW_RELATED => ! in_array($relationName, $requestedIncludes),
            ];
        }


        // static relations are for cases where custom objects do not have actual
        // relations that can be trace by eloquent.5
        // these should be treated separately

        if (is_a($this, ResourceStaticRelationsInterface::class)) {

            foreach ($this->getStaticRelations() as $relationName) {

                $relations[ Str::snake($relationName, '-') ] = [
                    SchemaProvider::SHOW_DATA    => false,
                    SchemaProvider::SHOW_RELATED => true,
                ];
            }
        }

        return $relations;
    }

    /**
     * Returns resource type to use for encoder
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->getResourceName();
    }

    /**
     * Returns resource sub-url to use for encoder
     *
     * @return string
     */
    public function getResourceSubUrl()
    {
        return '/';
    }

}
