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

namespace EasyFast\Orm;

//TODO: Refazer lógica para agilizar a criação das classes

use EasyFast\App;
use RuntimeException;
use EasyFast\Common\MXML;
use EasyFast\Common\Utils;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class GenerateClass
 * Faz a leitura do Schema XML e cria as classes e traits
 * @package EasyFast\ORM
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait GenerateClass
{
    /**
     * @var MXML Armagena o schema do banco de dados
     */
    private $schema;

    /**
     * @var array Armagena as tabelas estrangeira
     */
    private $foreignTable;

    /**
     * @var string Armagena o nome da classe
     */
    private $nameClass;

    /**
     * @var Armagena o namespace
     */
    private $namespace;

    /**
     * @var Armagena os metódos seters da classe atual
     */
    private $methodsSeters;

    /**
     * @var Armagena os metódos geters da classe atual
     */
    private $methodsGeters;

    /**
     * @var Armagena as propriedades da classe atual
     */
    private $propertys;

    /**
     * @var Armagena a estrutura da classe atual
     */
    private $structureClass;

    /**
     * @var string Armagena o diretorio principal onde serão gravada as classes
     */
    private $dir = 'Model';

    /**
     * @var bool Informa se o campo de chave estrangeira é lazyLoad ou não
     */
    private $lazyLoad;

    /**
     * Method setDir
     * Seta diretorio onde serão armagernado as classes
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $dir Diretorio onde serão armazenada as classes
     * @access public
     * @throws EasyFastException
     */
    public function setDir ($dir)
    {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Não foi possível criar o diretório: "' . $dir . '"');
            }
        }
        $this->dir = $dir;
    }

    /**
     * Method setXmlFile
     * Seta o arquivo XML fisico contendo o schema
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $xml
     */
    public function setXmlFile ($xml)
    {
        $this->schema = new MXML();
        $this->schema->load($xml);
    }

    /**
     * Method setSchema
     * Seta XML contendo o schema do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $xml
     */
    public function setSchema ($xml)
    {
        $this->schema = new MXML();
        $this->schema->loadXML($xml);
    }

    /**
     * Method createTraits
     * Cria estrutura das traits conforme arquivo Schema XML e armazena fisicamente
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function createTraits ()
    {
        $dir = $this->dir.'/Traits';
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Não foi possível criar o diretório: "' . $dir . '"');
            }
        }

        $this->namespace = ucfirst(implode('\\', explode('/', $dir)));

        foreach ($this->schema->getTag('table') as $table) {

            $this->methodsGeters = null;
            $this->methodsSeters = null;
            $this->propertys     = null;
            $uses                = null;
            $this->lazyLoad      = null;
            $this->foreignTable  = [];
            $this->nameClass     = Utils::snakeToCamelCase($table->getAttribute('name'));

            if ($table->hasChildNodes()) {

                foreach ($table->childNodes as $columns) {

                    if ($columns->tagName == 'column') {

                        $columnName  = $columns->getAttribute('name');
                        $columnValue = $columns->getAttribute('valueDefault');
                        $property    = lcfirst(Utils::snakeToCamelCase($columnName));

                        if (!empty($columnValue)) {
                            $property = "{$property} = '{$columnValue}'";
                        }

                        $this->propertys .= "\tprotected \$" . $property . ";\n";
                        $this->methodSet($columnName);
                        $this->methodGet($columnName);

                    } elseif ($columns->tagName == 'foreign-key') {
                        foreach ($columns->childNodes as $fk) {

                            // Checa se existe mais de uma chave estrangeira para uma mesma tabela, caso exista concatena com o nome do campo
                            $countOccurrence = $this->schema->query('foreign-key[foreignTable="' . $columns->getAttribute('foreignTable') . '"]', $columns)->length;

                            $class = Utils::snakeToCamelCase($columns->getAttribute('foreignTable'));

                            if ($countOccurrence > 1) {
                                $ft = $columns->getAttribute('foreignTable') . ucfirst($fk->getAttribute('local'));
                            } else {
                                $ft = $columns->getAttribute('foreignTable');
                            }

                            $this->propertys .= "\n\t/**\n";
                            $this->propertys .= "\t * @var {$class}\n";
                            $this->propertys .= "\t */\n";
                            $this->propertys .= "\tprivate \$" . lcfirst(Utils::snakeToCamelCase($ft)) . ";\n";
                            $this->methodSetFt($fk->getAttribute('foreign'), $fk->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class);
                            $this->methodGetFt($fk->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class);
                            $this->lazyLoad = $fk->getAttribute('lazyLoad');

                            if (empty($this->foreignTable[$columns->getAttribute('foreignTable')])) {
                                $this->foreignTable[$columns->getAttribute('foreignTable')] = $fk->getAttribute('local');
                                $uses .= "use {$this->dir}\\" . Utils::snakeToCamelCase($columns->getAttribute('foreignTable')) . ";\n";
                            }

                        }
                    } elseif ($columns->tagName == 'child') {
                        foreach ($columns->childNodes as $child) {

                            // Checa se existe mais de uma chave estrangeira para uma mesma tabela, caso exista concatena com o nome do campo
                            $countOccurrence = $this->schema->query('child[foreignTable="' . $columns->getAttribute('foreignTable') . '"]', $columns)->length;

                            $class = Utils::snakeToCamelCase($columns->getAttribute('foreignTable'));

                            if ($countOccurrence > 1) {
                                $ft = $columns->getAttribute('foreignTable') . ucfirst($child->getAttribute('local')) . '_M';
                            } else {
                                $ft = $columns->getAttribute('foreignTable') . '_M';
                            }

                            $this->methodSetChild($child->getAttribute('foreign'), $child->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class);
                            $this->methodGetChild($child->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class, $child->getAttribute('foreign'));
                            $this->lazyLoad = $child->getAttribute('lazyLoad');

                            if (empty($this->foreignTable[$columns->getAttribute('foreignTable')])) {
                                $this->foreignTable[$columns->getAttribute('foreignTable')] = $child->getAttribute('local');
                                $uses .= "use {$this->dir}\\" . Utils::snakeToCamelCase($columns->getAttribute('foreignTable')) . ";\n";
                            }

                        }
                    }
                }
            }

            try {
                $file = new \SplFileObject("{$dir}/Trait$this->nameClass.class.php", 'w');
                $file->fwrite($this->structureTrait($uses));
            } catch (RuntimeException $e) {
                throw new EasyFastException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * Method createClass
     * Cria estrutura das classes conforme arquivo Schema XML e armazena fisicamente
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function createClass ()
    {
        $dir = $this->dir;
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Não foi possível criar o diretório: "' . $dir . '"');
            }
        }

        $this->namespace = ucfirst(implode('\\', explode('/', $this->dir)));

        foreach ($this->schema->getTag('table') as $table) {

            $this->nameClass = Utils::snakeToCamelCase($table->getAttribute('name'));

            if (!file_exists("{$this->dir}/{$this->nameClass}.class.php")) {
                $file = new \SplFileObject("{$this->dir}/{$this->nameClass}.class.php", 'w');
                $file->fwrite($this->structureClass());
            }

        }
    }

    /**
     * Method methodSet
     * Gera método Seter
     * @author Bruno Oliveira <bruno@salluzweb.com.br
     * @param string $columnName;
     */
    private function methodSet ($columnName)
    {
        $name = Utils::snakeToCamelCase($columnName);

        $v  = "\t/**";
        $v .= "\n\t * Method set{$name}";
        $v .= "\n\t * Atribui valor para propriedade " . lcfirst($name);
        $v .= "\n\t */";
        $v .= "\n\tpublic function set{$name} (\$val)";
        $v .= "\n\t{";
        $v .= "\n\t\t\$this->" . lcfirst($name) . ' = $val;';
        $v .= "\n\t\treturn \$this;";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsSeters .= $v;
    }

    /**
     * Method methodSetFt
     * Gera método seter para foreign table
     * @param string $ft Nome da tabela estrangeira
     * @param string $ftPhpName Apelido para o método
     * @param string $property
     * @param string $propLocal
     * @param string $table
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    private function methodSetFt ($property, $propLocal, $ft, $ftPhpName = null, $table)
    {
        $ftName    = empty($ftPhpName) ? $ft : $ftPhpName;
        $ftName    = Utils::snakeToCamelCase($ftName);
        $ft        = Utils::snakeToCamelCase($ft);
        $propLocal = Utils::snakeToCamelCase($propLocal);
        $property  = Utils::snakeToCamelCase($property);

        $v  = "\t/**";
        $v .= "\n\t * Method set$ftName";
        $v .= "\n\t * Atribui valor para a propriedade " . lcfirst($property);
        $v .= "\n\t */";
        $v .= "\n\tpublic function set$ftName ($table \$val)";
        $v .= "\n\t{";
        $v .= "\n\t\tif (\$val->get$property() == null) " . '{';
        $v .= "\n\t\t\t\$val->save();";
        $v .= "\n\t\t}";
        $v .= "\n\t\t\$this->set$propLocal(\$val->get$property());";
        $v .= "\n\t\treturn \$this;";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsSeters .= $v;
    }

    /**
     * Method methodSetChild
     * Gera método seter para foreign table
     * @param string $ft Nome da tabela estrangeira
     * @param string $ftPhpName Apelido para o método
     * @param string $property
     * @param string $propLocal
     * @param string $table
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    private function methodSetChild ($property, $propLocal, $ft, $ftPhpName = null, $table)
    {
        $ftName    = empty($ftPhpName) ? $ft : $ftPhpName;
        $ftName    = Utils::snakeToCamelCase($ftName);
        $propLocal = Utils::snakeToCamelCase($propLocal);
        $property  = Utils::snakeToCamelCase($property);

        $v  = "\t/**";
        $v .= "\n\t * Method set$ftName";
        $v .= "\n\t * Atribui valor para a propriedade " . lcfirst($property);
        $v .= "\n\t * @return {$table}";
        $v .= "\n\t */";
        $v .= "\n\tpublic function set$ftName ($table \$val = null)";
        $v .= "\n\t{";
        $v .= "\n\t\tif (!is_null(\$val)) {";
        $v .= "\n\t\t\t\$val->save();";
        $v .= "\n\t\t}";
        $v .= "\n\t\t\$model = new {$table};";
        $v .= "\n\t\t\$model->set{$propLocal}(\$this->get{$property}());";
        $v .= "\n\t\treturn \$model;";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsSeters .= $v;
    }

    /**
     * Method methodGet
     * Gera método geters
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $columnName;
     */
    private function methodGet ($columnName)
    {
        $name = Utils::snakeToCamelCase($columnName);

        $v  = "\t/**";
        $v .= "\n\t * Method get{$name}";
        $v .= "\n\t * Obtêm o valor para propriedade " . lcfirst($name);
        $v .= "\n\t */";
        $v .= "\n\tpublic function get{$name} ()";
        $v .= "\n\t{";
        $v .= "\n\t\treturn \$this->" . lcfirst($name) . ';';
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsGeters .= $v;
    }

    /**
     * Method methodGetFt
     * Gera método geters para foreign table
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    private function methodGetFt ($property, $ft, $ftPhpName = null, $class)
    {
        $ftName   = empty($ftPhpName) ? $ft : $ftPhpName;
        $ftName   = Utils::snakeToCamelCase($ftName);
        $ft       = Utils::snakeToCamelCase($ft);
        $propertyclass = lcfirst($ft);
        $property = lcfirst(Utils::snakeToCamelCase($property));

        $v  = "\t/**";
        $v .= "\n\t * Method get{$ftName}";
        $v .= "\n\t * Obtêm o objeto {$propertyclass}";
        $v .= "\n\t */";
        $v .= "\n\tpublic function get{$ftName} ()";
        $v .= "\n\t{";
        $v .= "\n\t\ttry {";
        $v .= "\n\t\t\t\$this->{$propertyclass} = new {$class}(\$this->$property);";
        $v .= "\n\t\t} catch (EasyFastException \$e) {";
        $v .= "\n\t\t\t\$this->{$propertyclass} = new {$class}();";
        $v .= "\n\t\t}";
        $v .= "\n\t\treturn \$this->{$propertyclass};";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsGeters .= $v;
    }

    /**
     * Method methodGetFt
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $propertyBd
     * @param $ft
     * @param null $ftPhpName
     * @param $class
     * @param $property
     */
    private function methodGetChild ($propertyBd, $ft, $ftPhpName = null, $class, $property)
    {
        $ftName   = empty($ftPhpName) ? $ft : $ftPhpName;
        $ftName   = Utils::snakeToCamelCase($ftName);
        $property = ucfirst(Utils::snakeToCamelCase($property));

        $v  = "\t/**";
        $v .= "\n\t * Method get{$ftName}";
        $v .= "\n\t */";
        $v .= "\n\tpublic function get{$ftName}(QueryObject \$where = null, \$limit = 1)";
        $v .= "\n\t{";
        $v .= "\n\t\ttry {";
        $v .= "\n\t\t\t\$models = new {$class}();";
        $v .= "\n\t\t\t\$models->where('{$propertyBd}', \$this->get{$property}());";
        $v .= "\n\t\t\tif (!is_null(\$where)) {";
        $v .= "\n\t\t\t\tforeach (\$where::getWhere() as \$w) {";
        $v .= "\n\t\t\t\t\t\$models->where(\$w['column'], \$w['operator'], \$w['value'], \$w['opLogic']);";
        $v .= "\n\t\t\t\t}";
        $v .= "\n\t\t\t\t\$where::cleanWhere();";
        $v .= "\n\t\t\t}";
        $v .= "\n\t\t\tif (!is_null(\$limit) AND \$limit > 1) {";
        $v .= "\n\t\t\t\treturn array_slice(\$models->find(), 0, \$limit);";
        $v .= "\n\t\t\t} elseif (!is_null(\$limit)) {";
        $v .= "\n\t\t\t\treturn \$models->find()[0];";
        $v .= "\n\t\t\t}";
        $v .= "\n\t\t\treturn \$models->find();";
        $v .= "\n\t\t} catch (EasyFastException \$e) {";
        $v .= "\n\t\t\tif (!is_null(\$limit) AND \$limit <= 1) {";
        $v .= "\n\t\t\t\treturn new {$class}();";
        $v .= "\n\t\t\t}";
        $v .= "\n\t\t\treturn array();";
        $v .= "\n\t\t}";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsGeters .= $v;
    }

    /**
     * Method structureTrait
     * Gera a estrutura das traits
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string
     * @param string $use declara uso de classes no contexto da trait
     */
    private function structureTrait ($use = null)
    {
        $v  = "<?php";
        $v .= "\n/** Generation by EasyFast Framework **/";
        $v .= "\nnamespace {$this->namespace};\n\n";

        $v .= "use EasyFast\\Exceptions\\EasyFastException;";
        $v .= "use EasyFast\\Common\\QueryObject;";
        !is_null($use) ? $v .= "{$use}" : null;

        $v .= "\n\n/**";
        $v .= "\n * Trait {$this->nameClass}";
        $v .= "\n * Contêm métodos geters seters";
        $v .= "\n */";
        $v .= "\ntrait Trait{$this->nameClass}";
        $v .= "\n{";
        $v .= "\n$this->propertys";
        $v .= "\n$this->methodsSeters";
        $v .= "\n$this->methodsGeters";
        $v .= "\n}";
        $v .= "\n";

        return $v;
    }

    /**
     * Method generateStructureClass
     * Responsável em criar a estrutura da classe
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string
     */
    private function structureClass ()
    {
        $v  = "<?php";
        $v .= "\n/** Generation by EasyFast Framework - " . date('Y-m-d H:i:s') . "**/";
        $v .= "\nnamespace {$this->namespace};";
        $v .= "\n\nuse EasyFast\\Mvc\\Model;";
        $v .= "\n\n/**";
        $v .= "\n * Class {$this->nameClass}";
        $v .= "\n * Contêm regras de négocio relacionado a este objeto";
        $v .= "\n */";
        $v .= "\nclass {$this->nameClass} extends Model";
        $v .= "\n{";
        $v .= "\n\tuse Traits\\Trait$this->nameClass;";
        $v .= "\n}";
        $v .= "\n";

        return $v;
    }

}