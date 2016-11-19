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

namespace EasyFast\Config;

include __DIR__ . '/../Config/AppConfig.class.php';
include __DIR__ . '/../Config/ViewConfig.class.php';
include __DIR__ . '/../Config/DataBaseConfig.class.php';
include_once __DIR__ . '/../Common/Utils.class.php';
include_once __DIR__ . '/../Exceptions/EasyFastException.class.php';

use EasyFast\Route;
use EasyFast\Common\Utils;
use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Config
 * Define as variaveis de configuração da aplicação
 * @package EasyFast\Common
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @version 1.2
 */
class Config
{
    use AppConfig;
    use ViewConfig;
    use DataBaseConfig;

    /**
     * setConfigFile
     * @param $file
     * @param string $ext
     */
    public function setConfigFile($file, $ext = 'ini')
    {
        if ($ext == 'ini') {
            $this->setConfigIni($file);
        }

        // TODO: Implement read config XML
    }

    /**
     * setConfigIni
     *
     * @param $file
     */
    private function setConfigIni($file)
    {
        $configFile = parse_ini_file($file, true);
        if (isset($configFile['App'])) {
            foreach ($configFile['App'] as $key => $app) {
                $this->setAppConfig($key, empty($app) ? false : $app);
            }
        }

        if (isset($configFile['DataBase'])) {
            foreach ($configFile['DataBase'] as $key => $db) {
                $this->setDataBaseConfig($key, $db);
            }
        }

        if (isset($configFile['View'])) {
            foreach ($configFile['View'] as $key => $db) {
                $this->setViewConfig($key, $db);
            }
        }
    }

    /**
     * setConfigFileRoute
     * @param $file
     */
    public function setConfigFileRoute($file)
    {
        $route = new Route();
        $route->setConfigFile($file);
    }
}
