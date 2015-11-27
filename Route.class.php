<?php
namespace EasyFast;

use EasyFast\Http\Restful;
use EasyFast\Common\Utils;
use EasyFast\Config\Config;
use EasyFast\Routes\RouteParent;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class Route
 * @package EasyFast\Common\Route
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Route extends RouteParent
{
    /**
     * Read file config
     *
     * @param $file
     */
    public function setConfigFile($file)
    {
        $controller = null;
        $handle = fopen($file, 'r');
        if ($handle) {
            $i = 0;
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);

                if (preg_match('/^[[A-Za-z0-9]+]$/', $buffer, $matches)) {
                    $controller = substr($matches[0], 1, -1);
                    $i = 0;
                } else {
                    if ($buffer == PHP_EOL) {
                        continue;
                    }
                    $configs = explode(',', $buffer);
                    foreach ($configs as $c) {
                        $c = explode('=>', $c);
                        self::$routes[$controller][$i][trim($c[0])] = trim($c[1]);
                    }
                    $i++;
                }
            }
        }
    }

    public function intercepRequests()
    {
        if (!isset($_GET['url']) && !App::getConfig()->App->RouteAutomatic) {
            $this->location($this->getRoute('Default', 'index')['url']);
        } elseif (App::getConfig()->App->RouteAutomatic) {
            $queryStrings = array_filter(explode('/', $_GET['url']));
            $nameClass = 'Controller\\' . ucfirst(Utils::hiphenToCamelCase($queryStrings[0]));

            if (!class_exists($nameClass)) {
                throw new EasyFastException("Class \"$nameClass\" not found.");
            }

            $class = new $nameClass;

            if (count($queryStrings) > 1) {
                $nameMethod = Utils::hiphenToCamelCase($queryStrings[1]);
                if (method_exists($class, $nameMethod)) {
                    unset($queryStrings[0]);
                    unset($queryStrings[1]);
                    call_user_func_array(array($class, $nameMethod), $queryStrings);
                } else {
                    $this->location($this->getRoute('Default', 'notfound')['url']);
                }
            } else {
                if (method_exists($class, 'view')) {
                    $class->view();
                } else {
                    $this->location($this->getRoute('Default', 'notfound')['url']);
                }
            }
        } else {
            $queryString = explode('/', $_GET['url']);
            $controller = ucfirst(Utils::hiphenToCamelCase($queryString[0]));
            try {
                $route = $this->getRoute($controller, $queryString[1]);
            } catch (EasyFastException $e) {
                $route = $this->getRoute('Default')->go('notfound');
                $this->location($this->getRoute('Default', 'notfound')['url']);
            }

        }
    }

    /**
     * Check if route is valid
     *
     * @param $controller
     * @param null $name
     * @return bool
     * @throws EasyFastException
     */
    private function checkExistsRoute($controller, $name = null)
    {
        $routes = self::getRoutes();
        if (empty($routes->{$controller})) {
            throw new EasyFastException("No existing route {$controller}");
        }
        if (!is_null($name)) {
            if (empty($routes->{$controller}[$name])) {
                throw new EasyFastException("No existing route {$controller}.{$name}");
            }
        }
        return true;
    }
}