<?php

namespace Suribit\GraphQLBundle\Support;


use Suribit\GraphQLBundle\GraphQL;

class Field extends AbstractSupport
{
    /** @var GraphQL */
    protected $manager = null;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function attributes()
    {
        return [];
    }

    public function type()
    {
        return null;
    }

    public function args()
    {
        return [];
    }

    protected function getResolver()
    {
        if(!method_exists($this, 'resolve'))
        {
            return null;
        }

        $resolver = array($this, 'resolve');
        return function() use ($resolver)
        {
            $args = func_get_args();
            return call_user_func_array($resolver, $args);
        };
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        $args = $this->args();

        $attributes = array_merge($this->attributes, [
            'args' => $this->args()
        ], $attributes);

        $type = $this->type();
        if(isset($type))
        {
            $attributes['type'] = $type;
        }

        $resolver = $this->getResolver();
        if(isset($resolver))
        {
            $attributes['resolve'] = $resolver;
        }

        return $attributes;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]) ? $attributes[$key]:null;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]);
    }
}