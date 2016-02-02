<?php
namespace Suribit\GraphQLBundle\ConfigDrivers\Yaml;

use Suribit\GraphQLBundle\ConfigDrivers\FileDriver;
use Symfony\Component\Yaml\Yaml;

class YamlDriver extends FileDriver
{
    protected function load()
    {
        $configMapping = Yaml::parse($this->getFileContent($this->getPath()));

        foreach ($configMapping as $type => $value) {
            switch ($type) {
                case 'types':
                    $this->types = $value;
                    break;
                case 'schema':
                    $this->schema = $value;
                    break;
                default:
                    throw new \UnexpectedValueException(sprintf('Unsupported key "%s"'));
            }
        }
    }
}
