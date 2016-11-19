<?php
namespace EasyFast\Config;

/**
 * Trait DataBaseConfig
 * @package EasyFast\Config
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait DataBaseConfig
{
    /**
     * @var object
     */
    protected static $dbConfig;

    /**
     * @param $name
     * @param $config
     */
    public function setDataBaseConfig($name, array $config)
    {
        self::$dbConfig = (object)array();
        self::$dbConfig->{$name} = (object)$config;
    }

    /**
     * @param null $name
     * @return mixed
     */
    public static function getDataBaseConfig($name = null)
    {
        if (is_null($name)) {
            return self::$dbConfig;
        }
        return self::$dbConfig->{$name};
    }
}
