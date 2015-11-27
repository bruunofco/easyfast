<?php
namespace EasyFast\Routes;

use EasyFast\Config\Config;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class RouteParent
 *
 * @package EasyFast\Routes
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
abstract class RouteParent
{
    /**
     * Contains the routes of settings
     *
     * @var array
     */
    protected static $routes;

    /**
     * Get Routes
     *
     * @return object
     */
    public static function getRoutes()
    {
        return (object)self::$routes;
    }

    /**
     * Directs the application to the URL specified
     *
     * @param $url
     * @param bool|false $extern
     * @throws EasyFastException
     */
    public static function location($url, $extern = false)
    {
        if ($extern) {
            header("Location: {$url}");
        } else {
            if (empty(Config::getConfig()->App->WebHost)) {
                throw new EasyFastException('Web Host not set in your settings file');
            }
            header('Location: ' . Config::getWebHost() . $url);
        }
    }

    /**
     * @param $controller
     * @param null $name
     * @return \stdClass
     * @throws EasyFastException
     */
    public static function getRoute($controller, $name = null)
    {
        if (empty(self::getRoutes()->{$controller})) {
            throw new EasyFastException("No existing route {$controller}");
        }

        if (is_null($name)) {
            $controllerRoute = new RouteController();
            $controllerRoute->route = self::getRoutes()->{$controller};
            return $controllerRoute;
        }

        foreach (self::getRoutes()->{$controller} as $key => $ctrl) {
            if (isset($ctrl['name']) && $ctrl['name'] == $name) {
                return self::getRoutes()->{$controller}[$key];
            } elseif (isset($ctrl['url']) && $ctrl['url'] == $name) {
                return self::getRoutes()->{$controller}[$key];
            }
        }
        throw new EasyFastException("No existing route {$controller}.{$name}");
    }
}