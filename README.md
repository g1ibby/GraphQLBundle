# Symfony 2 GraphQl Bundle

Use Facebook GraphQL with Symfony 2. This library port [laravel-graphql](https://github.com/Folkloreatelier/laravel-graphql).
It is based on the PHP implementation [here](https://github.com/webonyx/graphql-php). 

## Installation

**1-** Require the package via Composer in your `composer.json`.
```json
{
	"require": {
		"suribit/graphql-bundle": "*"
	}
}
```

**2-** Run Composer to install or update the new requirement.

```bash
$ composer install
```

or

```bash
$ composer update
```

**3-** Add the service provider to your `app/AppKernel.php` file
```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Suribit\GraphQLBundle\GraphQLBundle(),
        );

        // ...
    }

    // ...
}
```

**4-** Create Type `src/path your bundle/Types/Country.php`
```php
<?php

namespace Lgck\GraphQlBundle\Types;

use GraphQL\Type\Definition\Type as TypeBase;
use Suribit\GraphQLBundle\Support\Type;

class Country extends Type
{
    protected $attributes = [
        'name' => 'Country',
        'description' => 'A Country'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => TypeBase::nonNull(TypeBase::int()),
                'description' => 'The id of the country'
            ],
            'name' => [
                'type' => TypeBase::string(),
                'description' => 'The name of country'
            ],
            'status' => [
                'type' => TypeBase::int(),
                'description' => 'The status of country'
            ]
        ];
    }
}

```

**5-** Create Query `src/path your bundle/Queries/Country.php`
```php
<?php

namespace Lgck\GraphQlBundle\Queries;

use GraphQL\Type\Definition\Type;
use Suribit\GraphQLBundle\Support\Query;

class Country extends Query
{
    protected $attributes = [
        'name' => 'Country query'
    ];

    public function type()
    {
        return $this->manager->type('country');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
        ];
    }

    public function resolve($root, $args)
    {
        $em = $this->manager->em;   // Doctrine Entity Manager
        return [
            'id' => `,
            'name' => 'Russia',
            'status' => 1
        ];
    }
}
```

**6-** Create config for graphql schema `src/path your bundle/Resources/config/graphql.yml`
```yaml
types:
  country: 'Lgck\GraphQlBundle\Types\Country'

schema:
  query:
    country: 'Lgck\GraphQlBundle\Queries\Country'

  mutation: []
```

**7-** Edit the file `src/path your bundle/Resources/config/services.yml`
```yaml
services:
    lgck_graph_ql.mapping.driver.yaml:
        public: true
        class: Suribit\GraphQLBundle\ConfigDrivers\Yaml\YamlDriver
        arguments:
            - "%kernel.root_dir%/../src/path your bundle/Resources/config/graphql.yml"

    lgck_graph_ql.manager:
        class: Suribit\GraphQLBundle\GraphQL
        arguments:
            - @doctrine.orm.entity_manager
            - @lgck_graph_ql.mapping.driver.yaml
```

**8-** Create a controller that will be the starting point for processing the request
```php
<?php

// ...

class MainController extends Controller
{
    public function queryAction(Request $request)
    {
        $manager = $this->get('lgck_graph_ql.manager');
        $query = $request->request->get('query');
        try {
            $data = $manager->query($query);
        } catch (QueryException $e) {
            $response = new JsonResponse($e->getErrors(), 500);
            return $response;
        }

        $response = new JsonResponse($data);
        return $response;
    }
}    
```

**9-** Now it is possible to send a data request
```graphql
query FooBar {
  country(id: 1) {
    id, 
    name, 
    status 
  }
}
```

TODO:
1. Add the complete documentation
2. Add validation