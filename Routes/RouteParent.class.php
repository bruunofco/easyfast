<?php
namespace EasyFast\Routes;

use EasyFast\Config\Config;
use EasyFast\Exceptions\EasyFastException;
use EasyFast\Exceptions\RouteException;

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
     * Contains the route list
     *
     * @var array
     */
    protected static $routeList;

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
     * @param $routes
     */
    public static function setRoutes($routes)
    {
        self::$routes = $routes;
    }

    /**
     * Directs the application to the URL specified
     *
     * @param $url
     * @param bool|false $extern
     * @throws RouteException
     */
    public static function location($url, $extern = false)
    {
        if ($extern) {
            header("Location: {$url}");
        } else {
            try {
                header('Location: ' . Config::getWebHost() . $url);
            } catch (EasyFastException $e) {
                throw new RouteException('Web Host not set in your settings file.');
            }
        }
    }

    /**
     * Get Route
     *
     * @param $controller
     * @param null $name
     * @return RouteController
     * @throws RouteException
     */
    public static function getRoute($controller, $name = null)
    {
        if (empty(self::getRoutes()->{$controller})) {
            throw new RouteException("No existing route {$controller}");
        }

        if (is_null($name)) {
            $controllerRoute = new RouteController();
            $controllerRoute->routeConfig = self::getRoutes()->{$controller};
            return $controllerRoute;
        }

        foreach (self::getRoutes()->{$controller} as $key => $ctrl) {
            if (isset($ctrl['name']) && $ctrl['name'] == $name) {
                $controllerRoute = new RouteController();
                $controllerRoute->routeConfig = self::getRoutes()->{$controller}[$key];
                return $controllerRoute;
            } elseif (isset($ctrl['url']) && $ctrl['url'] == $name) {
                $controllerRoute = new RouteController();
                $controllerRoute->routeConfig = self::getRoutes()->{$controller}[$key];
                return $controllerRoute;
            }
        }
        throw new RouteException("No existing route {$controller}.{$name}");
    }
}
