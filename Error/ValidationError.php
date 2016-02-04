<?php

namespace Suribit\GraphQLBundle\Error;

use GraphQL\Error;
use GraphQL\FormattedError;

class ValidationError extends Error
{
    public $validator;

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function getValidatorMessages()
    {
        return $this->validator ? $this->validator->messages():[];
    }
}