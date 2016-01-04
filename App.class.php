<?php
/*
 * Copyright 2015 Bruno de Oliveira Francisco <bruno@salluzweb.com.br>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace EasyFast;

include __DIR__ . '/Config/Config.class.php';
include __DIR__ . '/Http/Restful.class.php';

use EasyFast\Common\Utils;
use EasyFast\Http\Restful;
use EasyFast\Common\Registry;
use EasyFast\Sessions\Session;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class App
 * Main framework class, contain utils methods for application
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast
 * @access public
 */
class App
{
    /**
     * Constantes do framework
     */
    CONST VERSION    = '1.0';
    CONST NAME_FW    = 'EasyFast';
    CONST NAME_SPACE = 'EasyFast';
    CONST SITE       = 'https://github.com/bruunofco/easyfast/';
    CONST AUTHOR     = 'Bruno Oliveira';

    /**
     * Armazena objeto Registry
     * @var object $registry
     */
    public static $registry;

    /**
     * @var array $route Armagena as configurações de rota
     */
    private static $route = array();

    /**
     * @var bool Accept restful
     */
    private static $restful;

    /**
     * Trait Config
     */
    use Config\Config;

    /**
     * Method run
     * Executa a aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return void
     */
    public function run ()
    {
        //Registra AutoLoader
        spl_autoload_register(array($this, 'loader'));
        //Inicia sessão
        if ($this->sessionAutoStart) {
            new Session();
        }

        //Instancia rota dinâmica
        if ($this->routeDynamic) {
            $this->route();
        }
    }

    /**
     * Method loader
     * Autoloader - inclue dinamicamente os arquivos requisitados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $fileName Nome do arquivo a ser incluido na aplicação
     * @return void
     * @throws EasyFastException
     */
    private function loader ($fileName)
    {
        if (PHP_OS == "Windows" || PHP_OS == "WINNT") {
            $separator = '\\';
        } else {
            $fileName = preg_filter("/\\\/", '/', $fileName);
            $separator = '/';
        }

        $fileCheck = explode($separator, $fileName);

        if($fileCheck[0] == self::NAME_SPACE) {
            unset($fileCheck[0]);
            $fileName = implode($separator, $fileCheck);
            require_once __DIR__ . "{$separator}{$fileName}.class.php";
        } else {
            if (file_exists(self::$appDir . "{$fileName}.class.php")) {
                require_once self::$appDir . "{$fileName}.class.php";
            } elseif (file_exists(strtolower(self::$appDir . "{$fileName}.class.php"))) {
                require_once strtolower(self::$appDir . "{$fileName}.class.php");
            }
        }
    }

    /**
     * Method route
     * Intancia as classes dinâmicamente conforme rota
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    private function route ()
    {
        if (!isset($_GET['url'])) {
            $index = isset(self::$route['index']) ? self::$route['index'] : null;
            $this->routeLocation($index);
        } else {
            $queryStrings = array_filter(explode('/', $_GET['url']));
            $nameClass = 'Controller';

            foreach ($queryStrings as $key => $qs) {
                unset($queryStrings[$key]);
                $nameClass .= '\\' . ucfirst(Utils::hiphenToCamelCase($qs));
                if (class_exists($nameClass)) {
                    break;
                }
            }

            if (!class_exists($nameClass) || $nameClass == 'Controller') {
                throw new EasyFastException("Class \"$nameClass\" not found.");
            }

            $class = new $nameClass;
            $queryStrings = array_values($queryStrings);
            if (count($queryStrings) > 0) {
                $nameMethod = Utils::hiphenToCamelCase($queryStrings[0]);
                if (method_exists($class, $nameMethod)) {
                    unset($queryStrings[0]);
                    call_user_func_array(array($class, $nameMethod), $queryStrings);
                } else {
                    throw new EasyFastException("Method \"$nameMethod\" not found.");
                }
            } else {
                if (method_exists($class, 'view')) {
                    $class->view();
                } else {
                    throw new EasyFastException('Error generating display.');
                }
            }
        }
    }

    /**
     * Method routeLocation
     * Altera a rota da aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $url
     */
    public static function routeLocation ($url)
    {
        header("Location: $url");
    }

    /**
     * Method routeDefault
     * Seta a rota inicial (index)
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $url
     */
    public static function routeIndex ($url)
    {
        self::$route['index'] = $url;
    }

    /**
     * Method acceptServerRestful
     * Enable accept server restful
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function acceptServerRestful ()
    {
        self::$restful = new Restful();
    }

    /**
     * Method getServerRestful
     * Get instance Restful
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @throws EasyFastException
     * @return Restful
     */
    public static function getServerRestful ()
    {
        if (!self::$restful instanceof Restful) {
            Restful::response(array('status' => 'error', 'message' => 'Server Restful disabled.'), 403);
            exit();
        }

        return self::$restful;
    }

    /**
     * Method execMethodBeforeRunApp
     * Add method execute before run app
     * @author Bruno Oliveira bruno@salluzweb.com.br>
     */
    public function execMethodBeforeRunApp ($class, $method, $params = array())
    {
        //Registra AutoLoader
        spl_autoload_register(array($this, 'loader'));

        if (!is_object($class)) {
            $class = new $class;
        }

        call_user_func_array(array($class, $method), $params);
    }

}
