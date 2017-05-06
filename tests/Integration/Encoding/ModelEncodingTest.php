<?php
namespace Czim\JsonApi\Test\Integration\Encoding;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Encoder\Encoder;
use Czim\JsonApi\Encoder\Factories\TransformerFactory;
use Czim\JsonApi\Encoder\Transformers\ModelTransformer;
use Czim\JsonApi\Repositories\ResourceRepository;
use Czim\JsonApi\Support\Validation\JsonApiValidator;
use Czim\JsonApi\Test\AbstractSeededTestCase;
use Czim\JsonApi\Test\Helpers\Models\TestAuthor;
use Czim\JsonApi\Test\Helpers\Models\TestComment;
use Czim\JsonApi\Test\Helpers\Models\TestPost;
use Czim\JsonApi\Test\Helpers\Models\TestSeo;
use Czim\JsonApi\Test\Helpers\Resources\TestAuthorResource;
use Czim\JsonApi\Test\Helpers\Resources\TestCommentResource;
use Czim\JsonApi\Test\Helpers\Resources\TestPostResource;
use Czim\JsonApi\Test\Helpers\Resources\TestPostResourceWithDefaults;
use Czim\JsonApi\Test\Helpers\Resources\TestSeoResource;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelEncodingTest
 *
 * @group encoding
 */
class ModelEncodingTest extends AbstractSeededTestCase
{

    /**
     * @test
     */
    function it_transforms_a_model_with_related_records()
    {
        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, new TestPostResource);
        $repository->register(TestComment::class, new TestCommentResource);
        $repository->register(TestAuthor::class, new TestAuthorResource);
        $repository->register(TestSeo::class, new TestSeoResource);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Some Basic Title',
                        'body'                 => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
                        'type'                 => 'notice',
                        'checked'              => true,
                        'description-adjusted' => 'Prefix: the best possible post for testing',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/comments',
                                'related' => '/api/test-posts/1/test-comments',
                            ],
                            'data' => [
                                ['id' => '1', 'type' => 'test-comments'],
                                ['id' => '2', 'type' => 'test-comments'],
                            ],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/main-author',
                                'related' => '/api/test-posts/1/test-authors',
                            ],
                            'data' => ['type' => 'test-authors', 'id' => '1'],
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/seo',
                                'related' => '/api/test-posts/1/test-seos',
                            ],
                            'data' => null,
                        ],
                        'related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                        'pivot-related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/pivot-related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                    ],
                ],
            ],
            $transformer->transform(TestPost::first())
        );
    }

    /**
     * @test
     */
    function it_transforms_a_model_with_empty_relations_data()
    {
        // Set up the model to clear relations.
        $model = TestPost::find(2);
        $model->test_author_id = null;
        $model->save();


        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, TestPostResource::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '2',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Elaborate Alternative Title',
                        'body'                 => 'Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa.',
                        'type'                 => 'news',
                        'checked'              => false,
                        'description-adjusted' => 'Prefix: some alternative testing post',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/comments',
                                'related' => '/api/test-posts/2/test-comments',
                            ],
                            'data' => [],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/main-author',
                                'related' => '/api/test-posts/2/test-authors',
                            ],
                            'data' => null,
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/seo',
                                'related' => '/api/test-posts/2/test-seos',
                            ],
                            'data' => null,
                        ],
                        'related' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/related',
                                'related' => '/api/test-posts/2/test-posts',
                            ],
                            'data' => [],
                        ],
                        'pivot-related' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/pivot-related',
                                'related' => '/api/test-posts/2/test-posts',
                            ],
                            'data' => [],
                        ],
                    ],
                ],
            ],
            $transformer->transform($model)
        );
    }

    /**
     * @test
     */
    function it_transforms_a_model_with_eager_loaded_data()
    {
        $model = TestPost::first();
        $model->load('comments', 'author', 'related', 'pivotRelated');


        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, TestPostResource::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Some Basic Title',
                        'body'                 => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
                        'type'                 => 'notice',
                        'checked'              => true,
                        'description-adjusted' => 'Prefix: the best possible post for testing',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/comments',
                                'related' => '/api/test-posts/1/test-comments',
                            ],
                            'data' => [
                                ['id' => '1', 'type' => 'test-comments'],
                                ['id' => '2', 'type' => 'test-comments'],
                            ],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/main-author',
                                'related' => '/api/test-posts/1/test-authors',
                            ],
                            'data' => ['type' => 'test-authors', 'id' => 1],
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/seo',
                                'related' => '/api/test-posts/1/test-seos',
                            ],
                            'data' => null,
                        ],
                        'related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                        'pivot-related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/pivot-related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                    ],
                ],
            ],
            $transformer->transform($model)
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_no_resource_is_registered_for_a_referenced_related_model()
    {
        // Add seo for model
        $model = TestPost::find(2);
        $model->test_author_id = null;
        $model->save();

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, TestPostResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        $encoder->encode($model);
    }


    // ------------------------------------------------------------------------------
    //      Morph Relation
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_transforms_a_model_with_an_empty_morph_to_relation()
    {
        $model = TestSeo::create(['slug' => 'orphan']);

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-seos',
                    'attributes' => [
                        'slug' => 'orphan',
                    ],
                    'relationships' => [
                        'seoable' => [
                            'links' => [
                                'self' => '/api/test-seos/1/relationships/seoable',
                            ],
                            'data' => null,
                        ],
                    ],
                ],
            ],
            $transformer->transform($model)
        );
    }

    // ------------------------------------------------------------------------------
    //      Sideloaded includes
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_transforms_a_model_with_sideloaded_includes()
    {
        // Add seo for model
        $model = TestPost::first();
        $seo = new TestSeo(['slug' => 'testing post 1']);
        $model->seo()->save($seo);

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        // Set the request for includes
        $encoder->setRequestedIncludes(['comments', 'main-author', 'seo']);

        $repository->register(TestPost::class, TestPostResource::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        $data = $encoder->encode($model);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Some Basic Title',
                        'body'                 => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
                        'type'                 => 'notice',
                        'checked'              => true,
                        'description-adjusted' => 'Prefix: the best possible post for testing',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/comments',
                                'related' => '/api/test-posts/1/test-comments',
                            ],
                            'data' => [
                                ['type' => 'test-comments', 'id' => '1'],
                                ['type' => 'test-comments', 'id' => '2'],
                            ],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/main-author',
                                'related' => '/api/test-posts/1/test-authors',
                            ],
                            'data' => ['type' => 'test-authors', 'id' => 1],
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/seo',
                                'related' => '/api/test-posts/1/test-seos',
                            ],
                            'data' => ['type' => 'test-seos', 'id' => '1'],
                        ],
                        'related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                        'pivot-related' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/pivot-related',
                                'related' => '/api/test-posts/1/test-posts',
                            ],
                            'data' => [
                                ['type' => 'test-posts', 'id' => '2'],
                                ['type' => 'test-posts', 'id' => '3'],
                            ],
                        ],
                    ],
                ],
                'included' => [
                    [
                        'id'         => '1',
                        'type'       => 'test-comments',
                        'attributes' => [
                            'title'       => 'Comment Title A',
                            'body'        => 'Lorem ipsum dolor sit amet.',
                            'description' => 'comment one',
                        ],
                        'relationships' => [
                            'author' => [
                                'links' => [
                                    'self'    => '/api/test-comments/1/relationships/author',
                                    'related' => '/api/test-comments/1/test-authors',
                                ],
                                'data' => ['type' => 'test-authors', 'id' => '2'],
                            ],
                            'post' => [
                                'links' => [
                                    'self'    => '/api/test-comments/1/relationships/post',
                                    'related' => '/api/test-comments/1/test-posts',
                                ],
                                'data' => ['type' => 'test-posts', 'id' => '1'],
                            ],
                            'seos' => [
                                'links' => [
                                    'self'    => '/api/test-comments/1/relationships/seos',
                                    'related' => '/api/test-comments/1/test-seos',
                                ],
                                'data' => [],
                            ],
                        ],

                    ],
                    [
                        'id'         => '2',
                        'type'       => 'test-comments',
                        'attributes' => [
                            'title'       => 'Comment Title B',
                            'body'        => 'Phasellus iaculis velit nec purus rutrum eleifend.',
                            'description' => 'comment two',
                        ],
                        'relationships' => [
                            'author' => [
                                'links' => [
                                    'self'    => '/api/test-comments/2/relationships/author',
                                    'related' => '/api/test-comments/2/test-authors',
                                ],
                                'data' => ['type' => 'test-authors', 'id' => '2'],
                            ],
                            'post' => [
                                'links' => [
                                    'self'    => '/api/test-comments/2/relationships/post',
                                    'related' => '/api/test-comments/2/test-posts',
                                ],
                                'data' => ['type' => 'test-posts', 'id' => '1'],
                            ],
                            'seos' => [
                                'links' => [
                                    'self'    => '/api/test-comments/2/relationships/seos',
                                    'related' => '/api/test-comments/2/test-seos',
                                ],
                                'data' => [],
                            ],
                        ],
                    ],
                    [
                        'id'         => '1',
                        'type'       => 'test-authors',
                        'attributes' => [
                            'name' => 'Test Testington',
                        ],
                        'relationships' => [
                            'posts' => [
                                'links' => [
                                    'self'    => '/api/test-authors/1/relationships/posts',
                                    'related' => '/api/test-authors/1/test-posts',
                                ],
                                'data' => [
                                    ['type' => 'test-posts', 'id' => '1'],
                                    ['type' => 'test-posts', 'id' => '2'],
                                ],
                            ],
                            'comments' => [
                                'links' => [
                                    'self'    => '/api/test-authors/1/relationships/comments',
                                    'related' => '/api/test-authors/1/test-comments',
                                ],
                                'data' => [
                                    ['type' => 'test-comments', 'id' => '3']
                                ],
                            ],
                        ],
                    ],
                    [
                        'id'         => '1',
                        'type'       => 'test-seos',
                        'attributes' => [
                            'slug' => 'testing post 1',
                        ],
                        'relationships' => [
                            'seoable' => [
                                'links' => [
                                    'self'    => '/api/test-seos/1/relationships/seoable',
                                    'related' => '/api/test-seos/1/test-posts'
                                ],
                                'data' => ['type' => 'test-posts', 'id' => '1'],
                            ],
                        ],
                    ],
                ],
            ],
            $data
        );

        static::assertTrue(
            (new JsonApiValidator)->validateSchema($data),
            'Generated array does not match JSON-API Schema'
        );
    }

    /**
     * @test
     */
    function it_transforms_a_model_with_sideloaded_includes_for_empty_relations_data()
    {
        // Clear the author relation
        $model = TestPost::find(2);
        $model->test_author_id = null;
        $model->save();

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        // Set the request for includes
        $encoder->setRequestedIncludes(['comments', 'main-author', 'seo']);

        $repository->register(TestPost::class, TestPostResource::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        $data = $encoder->encode($model);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '2',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Elaborate Alternative Title',
                        'body'                 => 'Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa.',
                        'type'                 => 'news',
                        'checked'              => false,
                        'description-adjusted' => 'Prefix: some alternative testing post',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/comments',
                                'related' => '/api/test-posts/2/test-comments',
                            ],
                            'data' => [],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/main-author',
                                'related' => '/api/test-posts/2/test-authors',
                            ],
                            'data' => null,
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/seo',
                                'related' => '/api/test-posts/2/test-seos',
                            ],
                            'data' => null,
                        ],
                        'related' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/related',
                                'related' => '/api/test-posts/2/test-posts',
                            ],
                            'data' => [],
                        ],
                        'pivot-related' => [
                            'links' => [
                                'self'    => '/api/test-posts/2/relationships/pivot-related',
                                'related' => '/api/test-posts/2/test-posts',
                            ],
                            'data' => [],
                        ],
                    ],
                ],
            ],
            $data
        );

        static::assertTrue(
            (new JsonApiValidator)->validateSchema($data),
            'Generated array does not match JSON-API Schema'
        );
    }


    // ------------------------------------------------------------------------------
    //      Defaults vs. requested includes
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_transforms_using_default_relations_set_in_a_resource()
    {
        // Add seo for model
        $model = TestPost::first();
        $seo = new TestSeo(['slug' => 'testing post 1']);
        $model->seo()->save($seo);

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, TestPostResourceWithDefaults::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        $data = $encoder->encode($model);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Some Basic Title',
                        'body'                 => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
                        'type'                 => 'notice',
                        'checked'              => true,
                        'description-adjusted' => 'Prefix: the best possible post for testing',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/comments',
                                'related' => '/api/test-posts/1/test-comments',
                            ],
                            'data' => [
                                ['type' => 'test-comments', 'id' => '1'],
                                ['type' => 'test-comments', 'id' => '2'],
                            ],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/main-author',
                                'related' => '/api/test-posts/1/test-authors',
                            ],
                            'data' => ['type' => 'test-authors', 'id' => 1],
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/seo',
                                'related' => '/api/test-posts/1/test-seos',
                            ],
                            'data' => ['type' => 'test-seos', 'id' => '1'],
                        ],
                    ],
                ],
                'included' => [
                    [
                        'id'         => '1',
                        'type'       => 'test-authors',
                        'attributes' => [
                            'name' => 'Test Testington',
                        ],
                        'relationships' => [
                            'posts' => [
                                'links' => [
                                    'self'    => '/api/test-authors/1/relationships/posts',
                                    'related' => '/api/test-authors/1/test-posts',
                                ],
                                'data' => [
                                    ['type' => 'test-posts', 'id' => '1'],
                                    ['type' => 'test-posts', 'id' => '2'],
                                ],
                            ],
                            'comments' => [
                                'links' => [
                                    'self'    => '/api/test-authors/1/relationships/comments',
                                    'related' => '/api/test-authors/1/test-comments',
                                ],
                                'data' => [
                                    ['type' => 'test-comments', 'id' => '3']
                                ],
                            ],
                        ],
                    ],
                    [
                        'id'         => '1',
                        'type'       => 'test-seos',
                        'attributes' => [
                            'slug' => 'testing post 1',
                        ],
                        'relationships' => [
                            'seoable' => [
                                'links' => [
                                    'self'    => '/api/test-seos/1/relationships/seoable',
                                    'related' => '/api/test-seos/1/test-posts'
                                ],
                                'data' => ['type' => 'test-posts', 'id' => '1'],
                            ],
                        ],
                    ],
                ],
            ],
            $data
        );
    }

    /**
     * @test
     */
    function it_transforms_ignoring_default_resource_relations_if_requested_includes_set_and_configured_to()
    {
        $this->app['config']->set('jsonapi.transform.requested-includes-cancel-defaults', true);

        // Add seo for model
        $model = TestPost::first();

        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);
        $this->app->instance(ResourceRepositoryInterface::class, $repository);

        $repository->register(TestPost::class, TestPostResourceWithDefaults::class);
        $repository->register(TestComment::class, TestCommentResource::class);
        $repository->register(TestAuthor::class, TestAuthorResource::class);
        $repository->register(TestSeo::class, TestSeoResource::class);

        $encoder->setRequestedIncludes(['seo']);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        $data = $encoder->encode($model);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-posts',
                    'attributes' => [
                        'title'                => 'Some Basic Title',
                        'body'                 => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
                        'type'                 => 'notice',
                        'checked'              => true,
                        'description-adjusted' => 'Prefix: the best possible post for testing',
                    ],
                    'relationships' => [
                        'comments' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/comments',
                                'related' => '/api/test-posts/1/test-comments',
                            ],
                            'data' => [
                                ['type' => 'test-comments', 'id' => '1'],
                                ['type' => 'test-comments', 'id' => '2'],
                            ],
                        ],
                        'main-author' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/main-author',
                                'related' => '/api/test-posts/1/test-authors',
                            ],
                            'data' => ['type' => 'test-authors', 'id' => 1],
                        ],
                        'seo' => [
                            'links' => [
                                'self'    => '/api/test-posts/1/relationships/seo',
                                'related' => '/api/test-posts/1/test-seos',
                            ],
                            'data' => null,
                        ],
                    ],
                ],
            ],
            $data
        );
    }

}
