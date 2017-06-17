<?php
namespace Czim\JsonApi\Http\Requests;

use Czim\JsonApi\Contracts\Support\Validation\JsonApiValidatorInterface;
use Czim\JsonApi\Data\Root;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Support\Request\RequestQueryParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class JsonApiRequest extends FormRequest
{

    /**
     * @var RequestQueryParser
     */
    protected $jsonApiQuery;

    /**
     * Data object tree with input data.
     *
     * @var Root|null
     */
    protected $rootData;

    /**
     * Whether to perform JSON Schema validation for the request.
     *
     * @var bool
     */
    protected $schemaValidation = true;

    /**
     * The type of schema validation to apply.
     *
     * @var string
     */
    protected $schemaValidationType = 'request';


    /**
     * {@inheritdoc}
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct();

        $this->jsonApiQuery = new RequestQueryParser($this);
    }

    /**
     * @return RequestQueryParser
     */
    public function jsonApiQuery()
    {
        return $this->jsonApiQuery;
    }

    /**
     * Returns data object tree for request body.
     *
     * @return Root
     */
    public function data()
    {
        if ( ! $this->rootData) {
            $this->rootData = new Root($this->all());
        }

        return $this->rootData;
    }

    /**
     * Default authorization: allow.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Default rules: none.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->validateAgainstSchema();

        parent::validate();
    }

    /**
     * Validates the request's contents against the relevant JSON Schema.
     */
    protected function validateAgainstSchema()
    {
        if (    ! $this->schemaValidation
            ||  ! $this->schemaValidationType
            ||  ! in_array($this->getMethod(), ['PATCH', 'POST', 'PUT'])
        ) {
            return;
        }

        $validator = $this->getSchemaValidator();

        if ( ! $validator->validateSchema($this->getInputSource()->all(), $this->schemaValidationType)) {

            throw (new JsonApiValidationException('JSON-API Schema validation error'))
                ->setErrors($validator->getErrors()->toArray());
        }
    }

    /**
     * @return JsonApiValidatorInterface
     */
    protected function getSchemaValidator()
    {
        return app(JsonApiValidatorInterface::class);
    }

}
