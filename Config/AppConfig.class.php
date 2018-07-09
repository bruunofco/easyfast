<?php
namespace EasyFast\Config;

use EasyFast\Common\Utils;
use EasyFast\Exceptions\EasyFastException;

/**
 * Trait AppConfig
 * @package EasyFast\Config
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait AppConfig
{
    /**
     * @var object
     */
    protected static $appConfig = array(
        'dir' => null,
        'sessionAutoStart' => true,
        'controllerDirectory' => 'Controller',
        'routeAutomatic' => true,
        'defaultController' => 'Main',
        'webHost' => null,
        'isWebService' => false,
        'configGeneral' => array()
    );

    /**
     * @param $property
     * @param $appConfig
     */
    public function setAppConfig($property, $appConfig)
    {
        $property = lcfirst($property);
        if (isset(self::$appConfig[$property])) {
            self::$appConfig[$property] = $appConfig;
        } else {
            self::$appConfig['configGeneral'][$property] = $appConfig;
        }
    }

    /**
     * @param null $name
     * @return object
     * @throws EasyFastException
     */
    public static function getAppConfig($name = null)
    {
        if (is_null($name)) {
            return self::$appConfig;
        }

        if (isset(self::$appConfig[$name])) {
            return self::$appConfig[$name];
        } elseif (isset(self::$appConfig['configGeneral'][$name])) {
            return self::$appConfig['configGeneral'][$name];
        } else {
            throw new EasyFastException("$name does not exist in the configuration file.");
        }
    }

    /**
     * @param $dir
     */
    public function setDir($dir)
    {
        if (preg_match('/[\\\\|\/]$/', $dir)) {
            self::$appConfig['dir'] = $dir;
        } else {
            self::$appConfig['dir'] = "$dir/";
        }
    }

    /**
     * Get WebHost Application
     *
     * @return string
     */
    public static function getWebHost()
    {
        $webhost = self::getAppConfig('webHost');
        if (preg_match('/[\\\\|\/]$/', $webhost)) {
            return $webhost;
        }
        return "$webhost/";
    }
}