<?php
namespace EasyFast;

use EasyFast\Exceptions\EasyFastException;
use stdClass;
use EasyFast\Common\Utils;
use EasyFast\Http\Restful;
use EasyFast\Routes\RouteParent;
use EasyFast\Exceptions\RouteException;

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
     * @return void
     */
    public function setConfigFile($file)
    {
        $controller = null;
        $handle = fopen($file, 'r');
        if ($handle) {
            $i = 0;
            $j = 0;
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = str_replace("\n", null, $buffer);
                if (preg_match('/^[[A-Za-z0-9[\\\\\]?]+]$/', $buffer, $matches)) {
                    $controller = substr($matches[0], 1, -1);
                    $j = 0;
                } else {
                    if ($buffer == PHP_EOL || empty($buffer)) {
                        continue;
                    }
                    $configs = explode(',', $buffer);
                    self::$routeList[$i]['controller'] = $controller;
                    foreach ($configs as $c) {
                        $c = explode('=>', $c);
                        self::$routeList[$i][trim($c[0])] = trim($c[1]);
                        self::$routes[$controller][$j][trim($c[0])] = trim($c[1]);
                    }
                    $i++;
                    $j++;
                }
            }
        }
    }

    /**
     * Intercept Requests
     *
     * @throws RouteException
     * @return mixed
     */
    public static function interceptRequests()
    {
        $controllerDir = App::getAppConfig('controllerDirectory');
        if (App::getAppConfig('routeDynamic')) {
            if (empty($_GET['url']) || $_GET['url'] == 'index') {
                $indexRoute = self::getRoute(App::getAppConfig('defaultController'), 'index');
                Utils::callMethodArgsOrder("{$controllerDir}\\" . App::getAppConfig('defaultController'), $indexRoute->routeConfig['action']);
            } else {
                $rest = new Restful();
                if (is_array(self::$routeList)) {
                    foreach (self::$routeList as $p) {
                        $class = "{$controllerDir}\\" . ucfirst(Utils::hiphenToCamelCase($p['controller']));
                        if ((isset($p['restful']) && $p['restful'] == 'true')) {
                            if (empty($p['request'])) {
                                throw new RouteException('To use restful with dynamic route is mandatory parameter request for this route in your routing configuration file.');
                            }
                            $rest->server($p['request'], $p['url'], array($class, $p['action']));
                        } elseif ($rest->checkUrl($p['url']) &&
                            ((isset($p['request']) && $p['request'] == $_SERVER['REQUEST_METHOD']) || empty($p['request']))
                        ) {
                            $data = new stdClass();
                            foreach ($rest->getQueryString() as $key => $val) {
                                $data->$key = $val;
                            }
                            try {
                                Utils::callMethodArgsOrder($class, $p['action'], (array)$data);
                                $httpStatus = isset($p['httpStatus']) ? $p['httpStatus'] : 200;
                                header("HTTP/1.1 {$httpStatus}");
                                return true;
                            } catch (EasyFastException $e) {
                                throw new RouteException("Controller {$class} notfound, impossible to continue with route.");
                            }
                        }
                    }
                    $indexRoute = self::getRoute(App::getAppConfig('defaultController'), 'notfound');
                    Utils::callMethodArgsOrder("{$controllerDir}\\" . App::getAppConfig('defaultController'), $indexRoute->routeConfig['action']);
                } else {
                    // TODO: Eliminar na próxima versão
                    $queryStrings = array_filter(explode('/', $_GET['url']));
                    $nameClass = $controllerDir;
                    foreach ($queryStrings as $key => $qs) {
                        unset($queryStrings[$key]);
                        $nameClass .= '\\' . ucfirst(Utils::hiphenToCamelCase($qs));
                        if (class_exists($nameClass)) {
                            break;
                        }
                    }
                    if (!class_exists($nameClass) || $nameClass == 'Controller') {
                        throw new RouteException("Class \"$nameClass\" not found.");
                    }
                    $class = new $nameClass;
                    $queryStrings = array_values($queryStrings);
                    if (count($queryStrings) > 0) {
                        $nameMethod = Utils::hiphenToCamelCase($queryStrings[0]);
                        if (method_exists($class, $nameMethod)) {
                            unset($queryStrings[0]);
                            call_user_func_array(array($class, $nameMethod), $queryStrings);
                        } else {
                            throw new RouteException("Method \"$nameMethod\" not found.");
                        }
                    } else {
                        if (method_exists($class, 'view')) {
                            $class->view();
                        } else {
                            throw new RouteException('Error generating display.');
                        }
                    }
                }
            }
        }
        return false;
    }
}
