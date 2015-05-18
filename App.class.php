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

use EasyFast\Common\Utils;
use EasyFast\Common\Registry;
use EasyFast\Sessions\Session;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class App
 * main framework class, contain utils methods for application
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
        //Instancia classe Registry
        static::$registry = Registry::getInstance();
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
            } else {
                //throw new EasyFastException("Classe $fileName inexistente.");
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
        if (isset($_GET['url'])) {
            $queryStrings = array_filter(explode('/', $_GET['url']));
            try {
                if (count($queryStrings) == 1) {
                    $nameClass = 'Controller\\'.ucfirst(Utils::hiphenToCamelCase($queryStrings[0]));
                    if (class_exists($nameClass)) {
                        $class = new $nameClass;
                        if (method_exists($class, 'View')) {
                            $class->view();
                        } else {
                            throw new EasyFastException('Erro ao gerar visualização.');
                        }
                    } else {
                        throw new EasyFastException("Classe \"$nameClass\" inexistente ou inválida.");
                    }
                } elseif (count($queryStrings) >= 2) {
                    $nameClass = 'Controller\\'.ucfirst(Utils::hiphenToCamelCase($queryStrings[0]));
                    $nameMethod = Utils::hiphenToCamelCase($queryStrings[1]);
                    if (class_exists($nameClass)) {
                        $class = new $nameClass;
                        if (method_exists($class, $nameMethod)) {
                            $class->$nameMethod();
                        } else {
                            throw new EasyFastException('Método inexistente ou inválido.');
                        }
                    } else {
                        throw new EasyFastException("Classe \"$nameClass\" inexistente ou inválida.");
                    }
                }
            } catch (EasyFastException $e) {
                echo $e->getMessage();
            }
        } else {
            $index = isset(self::$route['index']) ? self::$route['index'] : null;
            $this->routeLocation($index);
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

}
