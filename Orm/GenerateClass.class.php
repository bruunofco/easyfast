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
 * Reads the XML Schema and create classes and traits
 * @package EasyFast\ORM
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait GenerateClass
{
    /**
     * @var MXML Stores the database schema
     */
    private $schema;

    /**
     * @var array Stores the foreign tables
     */
    private $foreignTable;

    /**
     * @var string Stores the class name
     */
    private $nameClass;

    /**
     * @var Stores the namespace
     */
    private $namespace;

    /**
     * @var Stores setters methods of the current class
     */
    private $methodsSeters;

    /**
     * @var Stores getters methods of the current class
     */
    private $methodsGeters;

    /**
     * @var Stores the properties of the current class
     */
    private $propertys;

    /**
     * @var Stores the structure of the current class
     */
    private $structureClass;

    /**
     * @var string Stores the main directory where the classes will be recorded
     */
    private $dir = 'Model';

    /**
     * @var bool Tells whether the foreign key field is LazyLoad or not
     */
    private $lazyLoad;

    /**
     * Method setDir
     * Arrow directory where they are stored classes
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $dir Directory where they are stored classes
     * @access public
     * @throws EasyFastException
     */
    public function setDir ($dir)
    {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Could not create the directory: "' . $dir . '"');
            }
        }
        $this->dir = $dir;
    }

    /**
     * Method setXmlFile
     * Sets the physical XML file containing the schema
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
     * Arrow containing the XML database schema
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
     * Create traits of structure as XML Schema file and physically store
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function createTraits ()
    {
        $dir = $this->dir.'/Traits';
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Could not create the directory: "' . $dir . '"');
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

                            // Check if there is more than one foreign key to the same table, if any concatenate with the field name
                            $countOccurrence = $this->schema->query('foreign-key[foreignTable="' . $columns->getAttribute('foreignTable') . '"]', $columns)->length;

                            $class = Utils::snakeToCamelCase($columns->getAttribute('foreignTable'));
                            
                            if ($countOccurrence > 1) {
                                $ft = $columns->getAttribute('foreignTable') . ucfirst($fk->getAttribute('local'));
                            } else {
                                $ft = $columns->getAttribute('foreignTable');
                            }

                            $this->methodSetFt($fk->getAttribute('foreign'), $fk->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class);
                            $this->methodGetFt($fk->getAttribute('local'), $ft, $columns->getAttribute('phpName'), $class);
                            $this->lazyLoad = $fk->getAttribute('lazyLoad');

                            if (empty($this->foreignTable[$columns->getAttribute('foreignTable')])) {
                                $this->foreignTable[$columns->getAttribute('foreignTable')] = $fk->getAttribute('local');
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
     * Create the class structure as XML Schema file and physically store
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function createClass ()
    {
        $dir = $this->dir;
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true)) {
                throw new EasyFastException('Could not create the directory: "' . $dir . '"');
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
        $v .= "\n\t * Assign value to property " . lcfirst($name);
        $v .= "\n\t */";
        $v .= "\n\tpublic function set{$name} (\$val)";
        $v .= "\n\t{";
        $v .= "\n\t\t\$this->" . lcfirst($name) . ' = $val;';
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsSeters .= $v;
    }

    /**
     * Method methodSetFt
     * Generates setter method for foreign table
     * @param string $ft Name of the foreign table
     * @param string $ftPhpName Nickname for the method
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
        $v .= "\n\t * Assign value to the property " . lcfirst($property);
        $v .= "\n\t */";
        $v .= "\n\tpublic function set$ftName ($table \$val)";
        $v .= "\n\t{";
        $v .= "\n\t\t\$this->tmpObject{$ftName} = null;";
        $v .= "\n\t\tif (\$val->get$property() == null) " . '{';
        $v .= "\n\t\t\t\$val->save();";
        $v .= "\n\t\t}";
        $v .= "\n\t\t\$this->set$propLocal(\$val->get$property());";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsSeters .= $v;
    }

    /**
     * Method methodGet
     * Generate getters methods
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
     * Generate Getters methods to foreign table
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    private function methodGetFt ($property, $ft, $ftPhpName = null, $class)
    {
        $ftName   = empty($ftPhpName) ? $ft : $ftPhpName;
        $ftName   = Utils::snakeToCamelCase($ftName);
        $ft       = Utils::snakeToCamelCase($ft);
        $property = lcfirst(Utils::snakeToCamelCase($property));

        $v  = "\t/**";
        $v .= "\n\t * Method get{$ftName}";
        $v .= "\n\t * Obtêm o objeto " . lcfirst($ft);
        $v .= "\n\t */";
        $v .= "\n\tprivate \$tmpObject{$ftName} = null;";
        $v .= "\n\tpublic function get{$ftName} ()";
        $v .= "\n\t{";
        $v .= "\n\t\tif(is_null(\$this->tmpObject{$ftName})) {";
        $v .= "\n\t\t\t\$this->tmpObject{$ftName} = new {$class}(\$this->$property);";
        $v .= "\n\t\t}";
        $v .= "\n\t\treturn \$this->tmpObject{$ftName};";
        $v .= "\n\t}";
        $v .= "\n\n";

        $this->methodsGeters .= $v;
    }

    /**
     * Method structureTrait
     * Generates the structure of traits
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string
     * @param string $use declare the use of classes in the context of trait
     */
    private function structureTrait ($use = null)
    {
        $v  = "<?php";
        $v .= "\n/** Generation by EasyFast Framework **/";
        $v .= "\nnamespace {$this->namespace};";

        !is_null($use) ? $v .= "\n\n{$use}" : null;

        $v .= "\n\n/**";
        $v .= "\n * Trait {$this->nameClass}";
        $v .= "\n * They contain methods getters setters";
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
     * Responsible for creating the structure of the class
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
        $v .= "\n * They contain business rules related to this object";
        $v .= "\n */";
        $v .= "\nclass {$this->nameClass} extends Model";
        $v .= "\n{";
        $v .= "\n\tuse Traits\\Trait$this->nameClass;";
        $v .= "\n}";
        $v .= "\n";

        return $v;
    }

}
