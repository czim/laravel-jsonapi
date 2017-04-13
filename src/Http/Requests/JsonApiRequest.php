<?php
namespace Czim\JsonApi\Http\Requests;

use Czim\JsonApi\Contracts\Support\Validation\JsonApiValidatorInterface;
use Czim\JsonApi\Support\Request\RequestQueryParser;
use Illuminate\Foundation\Http\FormRequest;

class JsonApiRequest extends FormRequest
{

    /**
     * @var RequestQueryParser
     */
    protected $jsonApiQuery;

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
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->validateAgainstSchema();

        parent::validate();
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
     * Validates the request's contents against the relevant JSON Schema.
     */
    protected function validateAgainstSchema()
    {
        if ( ! $this->schemaValidation || ! $this->schemaValidationType) {
            return;
        }

        $validator = $this->getSchemaValidator();

        $validator->validateSchema($this->all(), $this->schemaValidationType);
    }

    /**
     * @return JsonApiValidatorInterface
     */
    protected function getSchemaValidator()
    {
        return app(JsonApiValidatorInterface::class);
    }

}
