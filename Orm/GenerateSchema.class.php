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

use EasyFast\Common\MXML;
use EasyFast\DataBase\Connection;
use EasyFast\Exceptions\EasyFastException;


/**
 * Class GenerateSchema
 * Faz a leitura do banco de dados e cria o schema XML
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\ORM
 */
trait GenerateSchema
{
    private $tables;
    private $tablesElement;
    private $attrNameTable;
    private $conn;
    private $xml;
    private $fileName;

    /**
     * @var array Values do not set as default
     */
    private $noValueDefault = array(
        'CURRENT_TIMESTAMP'
    );

    /**
     * Method __construct
     * Executa script SQL para resgatar informações sobre o banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $db
     * @throws EasyFastException
     * @return mixed
     */
    public function createSchema ($db = null)
    {
        $this->conn = new Connection($db);
        $stmt =  $this->conn->query("SELECT TABLE_NAME, COLUMN_NAME, TABLE_SCHEMA, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH,
                                     IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA
                                     FROM information_schema.COLUMNS
                                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN
                                     (SELECT TABLE_NAME FROM information_schema.TABLES T WHERE TABLE_SCHEMA = DATABASE())");

        $this->xml = new MXML('1.0', 'utf-8');
        $dataBase = $this->xml->createElement('database');
        $attrNameDB = $this->xml->createAttribute('name');
        $dataBase = $this->xml->appendChild($dataBase);
        $dataBase->appendChild($attrNameDB);

        while ($row = $stmt->fetchObject()) {
            $attrNameDB->value = $row->TABLE_SCHEMA;
            if (empty($this->tables[$row->TABLE_NAME])) {
                $this->tables[$row->TABLE_NAME] = $row->TABLE_NAME;
                $this->tablesElement = $this->xml->createElement('table');
                $this->attrNameTable = $this->xml->createAttribute('name', $row->TABLE_NAME);
                $this->tablesElement = $dataBase->appendChild($this->tablesElement);
                $this->tablesElement->appendChild($this->attrNameTable);
                $this->generateForeign($row->TABLE_NAME);
                $this->generateChild($row->TABLE_NAME);
            }
            $column = $this->xml->createElement('column');
            $attrName = $this->xml->createAttribute('name', $row->COLUMN_NAME);
            $attrType = $this->xml->createAttribute('type', $row->DATA_TYPE);
            $column = $this->tablesElement->appendChild($column);
            $column->appendChild($attrName);
            $column->appendChild($attrType);
            if (!empty($row->COLUMN_DEFAULT) && !in_array($row->COLUMN_DEFAULT, $this->noValueDefault)) {
                $valueDefault = $this->xml->createAttribute('valueDefault', $row->COLUMN_DEFAULT);
                $column->appendChild($valueDefault);
            }
            if (isset($row->CHARACTER_MAXIMUM_LENGTH)) {
                $attrLimit = $this->xml->createAttribute('size', $row->CHARACTER_MAXIMUM_LENGTH);
                $column->appendChild($attrLimit);
            }

            if ($row->IS_NULLABLE == 'NO') {
                $attrRequired = $this->xml->createAttribute('required', 'true');
                $column->appendChild($attrRequired);
            }

            if (isset($row->COLUMN_KEY) && $row->COLUMN_KEY == 'PRI') {
                $attrPRI = $this->xml->createAttribute('primaryKey', 'true');
                $column->appendChild($attrPRI);
            }

            if (isset($row->EXTRA) && $row->EXTRA == 'auto_increment') {
                $attrAutoIncrement = $this->xml->createAttribute('autoIncrement', 'true');
                $column->appendChild($attrAutoIncrement);
            }
        }

        $db = is_null($db) ? 'main': $db;
        $this->fileName = "schema_data_base_{$db}.xml";
        $this->xml->save($this->fileName);
        return $this->xml->saveXML();
    }


    /**
     * Method generateForeign
     * Resgata as chaves estrangeiras
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $table
     */
    private function generateForeign ($table)
    {
        $stmt2 =  $this->conn->query("SELECT i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.REFERENCED_TABLE_NAME,
                                      k.REFERENCED_COLUMN_NAME, k.COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS i
                                      LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                                      WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' AND i.TABLE_NAME = '$table' AND i.TABLE_SCHEMA = DATABASE() GROUP BY i.CONSTRAINT_NAME;");

        if ($stmt2->rowCount()) {
            while ($row2 = $stmt2->fetchObject()) {
                $foreignKey = $this->xml->createElement('foreign-key');
                $attrForeignTable = $this->xml->createAttribute('foreignTable', $row2->REFERENCED_TABLE_NAME);
                $reference = $this->xml->createElement('reference');
                $attrReferenceLocal = $this->xml->createAttribute('local', $row2->COLUMN_NAME);
                $attrReferenceForeign = $this->xml->createAttribute('foreign', $row2->REFERENCED_COLUMN_NAME);
                $attrLazyLoad = $this->xml->createAttribute('lazyLoad', 'true');

                $foreignKey = $this->tablesElement->appendChild($foreignKey);
                $foreignKey->appendChild($attrForeignTable);
                $reference = $foreignKey->appendChild($reference);
                $reference->appendChild($attrReferenceLocal);
                $reference->appendChild($attrReferenceForeign);
                $reference->appendChild($attrLazyLoad);
            }
        }
    }

    /**
     * Method generateChilds
     * Resgata as chaves estrangeiras
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $table
     */
    private function generateChild ($table)
    {
        $stmt2 =  $this->conn->query("SELECT
                                            i.TABLE_NAME,
                                            i.CONSTRAINT_TYPE,
                                            i.CONSTRAINT_NAME,
                                            k.REFERENCED_TABLE_NAME,
                                            k.REFERENCED_COLUMN_NAME,
                                            k.COLUMN_NAME
                                        FROM
                                            information_schema.TABLE_CONSTRAINTS i
                                                LEFT JOIN
                                            information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                                        WHERE
                                            i.CONSTRAINT_TYPE = 'FOREIGN KEY'
                                                AND k.REFERENCED_TABLE_NAME = '{$table}'
                                                AND i.TABLE_SCHEMA = DATABASE() GROUP BY i.TABLE_NAME;");

        if ($stmt2->rowCount()) {
            while ($row2 = $stmt2->fetchObject()) {
                $child = $this->xml->createElement('child');
                $attrForeignTable = $this->xml->createAttribute('foreignTable', $row2->TABLE_NAME);
                $reference = $this->xml->createElement('reference');
                $attrReferenceLocal = $this->xml->createAttribute('local', $row2->COLUMN_NAME);
                $attrReferenceForeign = $this->xml->createAttribute('foreign', $row2->REFERENCED_COLUMN_NAME);
                $attrLazyLoad = $this->xml->createAttribute('lazyLoad', 'true');

                $child = $this->tablesElement->appendChild($child);
                $child->appendChild($attrForeignTable);
                $reference = $child->appendChild($reference);
                $reference->appendChild($attrReferenceLocal);
                $reference->appendChild($attrReferenceForeign);
                $reference->appendChild($attrLazyLoad);
            }
        }
    }
}
