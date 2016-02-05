<?php

namespace Suribit\GraphQLBundle\Support;


use GraphQL\Type\Definition\ObjectType;

class Type extends AbstractSupport
{
    protected static $instances = [];

    public $original = null;

    public function attributes()
    {
        return [];
    }

    public function fields()
    {
        return [];
    }

    public function interfaces()
    {
        return [];
    }

    protected function getFieldResolver($name, $field)
    {
        if(isset($field['resolve']))
        {
            return $field['resolve'];
        }
        else if(method_exists($this, 'resolve'.$this->studly($name).'Field'))
        {
            $resolver = array($this, 'resolve'.$this->studly($name).'Field');
            return function() use ($resolver)
            {
                $args = func_get_args();
                return call_user_func_array($resolver, $args);
            };
        }

        return null;
    }

    public function getFields()
    {
        $fields = $this->fields();
        $allFields = [];
        foreach($fields as $name => $field)
        {
            if(is_string($field))
            {
                $field = new Field($field);
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            }
            else
            {
                $resolver = $this->getFieldResolver($name, $field);
                if($resolver)
                {
                    $field['resolve'] = $resolver;
                }
                $allFields[$name] = $field;
            }
        }

        return $allFields;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        $fields = $this->getFields();
        $interfaces = $this->interfaces();

        $attributes = array_merge($this->attributes, [
            'fields' => $fields
        ], $attributes);

        if(sizeof($interfaces))
        {
            $attributes['interfaces'] = $interfaces;
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

    public function toType()
    {
        $this->original = new ObjectType($this->toArray());
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
