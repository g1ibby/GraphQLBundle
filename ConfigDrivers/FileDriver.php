<?php

namespace Suribit\GraphQLBundle\ConfigDrivers;

abstract class FileDriver
{
    /**
     * @var string
     */
    private $path;
    protected $types;
    protected $schema;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->path = $file;
        $this->load();
    }

    /**
     * @param string $path
     * @return string
     * @throws \Exception
     */
    protected function getFileContent($path)
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('Config file "%s" not found', $path));
        }

        return file_get_contents($path);
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
