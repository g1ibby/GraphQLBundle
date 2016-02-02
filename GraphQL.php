<?php

namespace Suribit\GraphQLBundle;


use Doctrine\ORM\EntityManager;
use GraphQL\Error;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use Suribit\GraphQLBundle\ConfigDrivers\Yaml\YamlDriver;
use Suribit\GraphQLBundle\Support\Type;

class GraphQL
{
    public $em;
    protected $config;

    protected $mutations = [];
    protected $queries = [];
    protected $types = [];
    protected $typesInstances = [];

    public function __construct(EntityManager $em, YamlDriver $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * @return Schema
     */
    public function schema()
    {
        $this->typesInstances = [];

        $schema = $this->config->getSchema();
        $types = $this->config->getTypes();

        foreach($types as $name => $type)
        {
            $this->addType($type, $name);
            $this->type($name);
        }

        $configQuery = $schema['query'];
        $configMutation = $schema['mutation'];

        $queryFields = array_merge($configQuery, $this->queries);
        /** @var ObjectType $queryType */
        $queryType = $this->buildTypeFromFields($queryFields, [
            'name' => 'Query'
        ]);

        $mutationFields = array_merge($configMutation, $this->mutations);
        /** @var ObjectType $queryType */
        $mutationType = $this->buildTypeFromFields($mutationFields, [
            'name' => 'Mutation'
        ]);

        return new Schema($queryType, $mutationType);
    }

    /**
     * @param array $fields
     * @param array $opts
     * @return ObjectType
     */
    protected function buildTypeFromFields($fields, $opts = [])
    {
        if (empty($fields)) {
            return null;
        }

        $typeFields = [];
        foreach($fields as $key => $field)
        {
            if(is_string($field))
            {
                $typeFields[$key] = (new $field($this))->toArray();
            }
            else
            {
                $typeFields[$key] = $field;
            }
        }

        return new ObjectType(array_merge([
            'fields' => $typeFields
        ], $opts));
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function query($query, $params = [])
    {
        $executionResult = $this->queryAndReturnResult($query, $params);

        if (!empty($executionResult->errors))
        {
            return [
                'data' => $executionResult->data,
                'errors' => array_map([$this, 'formatError'], $executionResult->errors)
            ];
        }
        else
        {
            return [
                'data' => $executionResult->data
            ];
        }
    }

    public function queryAndReturnResult($query, $params = [])
    {
        $schema = $this->schema();
        $result = GraphQLBase::executeAndReturnResult($schema, $query, null, $params);
        return $result;
    }

    public function addMutation($name, $mutator)
    {
        $this->mutations[$name] = $mutator;
    }

    public function addQuery($name, $query)
    {
        $this->queries[$name] = $query;
    }

    public function addType($class, $name = null)
    {
        if(!$name)
        {
            $type = is_object($class) ? $class : new $class();
            $name = $type->name;
        }

        $this->types[$name] = $class;
    }

    public function type($name, $fresh = false)
    {
        if(!isset($this->types[$name]))
        {
            throw new \Exception('Type '.$name.' not found.');
        }

        if(!$fresh && isset($this->typesInstances[$name]))
        {
            return $this->typesInstances[$name];
        }

        /** @var Type $type */
        $type = $this->types[$name];
        if(!is_object($type))
        {
            $type = new $type();
        }

        $instance = $type->toType();
        $this->typesInstances[$name] = $instance;

        //Check if the object has interfaces
        if($type->interfaces)
        {
            InterfaceType::addImplementationToInterfaces($instance);
        }

        return $instance;
    }

    public function formatError(Error $e)
    {
        $error = [
            'message' => $e->getMessage()
        ];

        $locations = $e->getLocations();
        if(!empty($locations))
        {
            $error['locations'] = array_map(function($loc) { return $loc->toArray();}, $locations);
        }

        return $error;
    }
}