<?php
namespace EasyFast\Config;

use EasyFast\App;
use EasyFast\Common\Utils;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class ViewConfig
 * @package EasyFast\Config
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait ViewConfig
{
    /**
     * @var array
     */
    protected static $viewConfig = array(
        'dirTpl' => null,
        'title' => App::NAME_FW,
        'configGeneral' => array()
    );

    /**
     * @param $property
     * @param $viewConfig
     */
    public function setViewConfig($property, $viewConfig)
    {
        self::$viewConfig = Utils::arrayToObject(self::$viewConfig);
        $property = lcfirst($property);
        if (isset(self::$viewConfig->{$property})) {
            self::$viewConfig->{$property} = $viewConfig;
        } else {
            self::$viewConfig->configGeneral->{$property} = $viewConfig;
        }
        self::$viewConfig = (array)self::$viewConfig;
    }

    /**
     * @param null $name
     * @return mixed
     * @throws EasyFastException
     */
    public static function getViewConfig($name = null)
    {
        if (is_null($name)) {
            return self::$viewConfig;
        }

        if (array_key_exists($name, self::$viewConfig)) {
            return self::$viewConfig[$name];
        } elseif (isset(self::$viewConfig->configGeneral[$name])) {
            return self::$viewConfig->configGeneral[$name];
        } else {
            throw new EasyFastException("$name does not exist in the configuration file.");
        }
    }

    /**
     * @param $dir
     */
    public function setDirTpl($dir)
    {
        if (!preg_match('/[\\\\|\/]$/', $dir)) {
            $dir = "$dir/";
        }

        if (is_array(self::$viewConfig)) {
            self::$viewConfig['dirTpl'] = $dir;
        } else {
            self::$viewConfig->dirTpl = $dir;
        }
    }
}