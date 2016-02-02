<?php
namespace Suribit\GraphQLBundle\Exception;

use GraphQL\FormattedError;

class QueryException extends \RuntimeException
{
    /**
     * @var FormattedError[]
     */
    private $errors;

    /**
     * @param FormattedError[] $errors
     * @param string           $message
     * @param int              $code
     * @param \Exception|null  $previous
     */
    public function __construct(array $errors, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return FormattedError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
