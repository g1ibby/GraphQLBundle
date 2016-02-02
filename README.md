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

