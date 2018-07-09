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

namespace EasyFast\Mvc;

require __DIR__ . '/../Libs/smarty/libs/Smarty.class.php';
require __DIR__ . '/../Libs/smarty/libs/SmartyBC.class.php';

use SmartyBC;
use EasyFast\App;

/**
 * Class View
 * Gera visualização
 * @author Bruno Oliveira <bruno@salluzweb.com.br
 */
class View extends SmartyBC
{
    /**
     * @var Armazena diretório de templates
     */
    private $dirTpl;

    /**
     * Method __construct
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function __construct ()
    {
        $dirTpl = isset($this->dirTpl) ? $this->dirTpl : App::getViewConfig('dirTpl');

        parent::__construct();
        $this->template_dir     = $dirTpl . '';
        $this->compile_dir      = $dirTpl . 'tpl_tmp/';
        $this->config_dir       = $dirTpl . 'tpl_config/';
        $this->cache_dir        = $dirTpl . 'tpl_cache/';

        $this->left_delimiter   = '[{';
        $this->right_delimiter  = '}]';

        $this->caching = 0;
        $this->compile_check = true;
        $this->debugging = false;

        $this->assign('appConfig', array_merge_recursive(App::getViewConfig(), App::getAppConfig()));
    }


    /**
     * Method setDirTpl
     * @param string $val
     * @author Bruno Oliveira <bruno@salluzweb.com.br>s
     */
    public function setDirTpl ($val)
    {
        $this->dirTpl = $val;

        $this->template_dir     = $val . '';
        $this->compile_dir      = $val . 'tpl_tmp/';
        $this->config_dir       = $val . 'tpl_config/';
        $this->cache_dir        = $val . 'tpl_cache/';
    }
}
