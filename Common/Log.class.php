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

namespace EasyFast\Common;

use App;
use SplFileObject;

class Log
{
    CONST ERROR   = 'error';
    CONST WARNING = 'warning';
    CONST ACCESS  = 'access';

    protected $msg;
    protected $fileName;
    protected $dir;
    protected $type;

    /**
     * Method __contruct
     * Atribui menssagem ao log e nome do arquivo de log
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $type Tipo do log
     * @param string $msg Messagem que ocasionou a exception
     * @param string|null $fileName Nome do arquivo de log
     */
    public function __construct ($type, $msg, $fileName = null)
    {
        // $this->msg      = $msg;
        // // $this->dir      = App::$dirLog;

        // if (is_null($this->fileName = $fileName)) {
        //     $this->fileName = 'easyFast_'.lcfirst($type).'_'.date('dmA').'.log';
        // }
    }

    /**
     * Method setDir
     * Seta diretório de armagenamento do logo atual
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $dir Endereço do diretório
     * @return void
     */
    public function setDir ($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Method save
     * Salva logo no formato TXT
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return void
     */
    public function save ()
    {
        // if (!file_exists($this->dir)) {
        //     mkdir($this->dir);
        // }

        // $msg = null;
        // if (!file_exists("{$this->dir}{$this->fileName}")) {
        //     $msg = App::NAME_FW . " Framework Generation " . App::SITE ." \n\n";

        // }
        // $msg .= "[".date('Y-m-d H:i:s')."] :: [".__FILE__."] :: {$this->msg} \n";

        // try {
        //     $file = new SplFileObject("{$this->dir}{$this->fileName}", "w+");
        //     $file->fwrite($msg);
        // } catch (Exception $e) {
        //     echo $e->getMessage(); die;
        // }

    }

    /**
     * Method saveXML
     *
     */
    public function saveXML ()
    {
        //TODO: Cria padrão XML
    }

    /**
     * Method saveHTML
     *
     */
    public function saveHTML ()
    {
        //TODO: Cria padrão HTML
    }
}