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

use EasyFast\Exceptions\EasyFastException;

/**
 * Class Upload
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Upload
{
    private $file;
    private $extension;
    private $fileName;

    /**
     * Method __construct
     * @param array $fileTmp $_FILE[]
     * @access public
     */
    public function __construct($fileTmp)
    {
        $this->file = $fileTmp;
        $this->extension();
    }

    /**
     * @param $width
     * @param $height
     * @throws EasyFastException
     */
    public function resizeImg($width, $height)
    {
        $width  = intval($width);
        $height = intval($height);

        $typeImage = array('png', 'gif', 'jpg', 'jpeg');
        if (!in_array($this->extension, $typeImage)) {
            throw new EasyFastException('Extensão da imagem não é suportado.');
        }

        $sizeImg = getimagesize($this->file['tmp_name']);

        if ($width > $height) {
            $height = round(($sizeImg[1] / $sizeImg[0]) * $width);
        } else {
            $width = round(($sizeImg[0] / $sizeImg[1]) * $height);
        }

        $newImage = imagecreatetruecolor($width, $height);

        switch ($this->extension) {
            case 'png':
                $image = imagecreatefrompng($this->file['tmp_name']);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $sizeImg[0], $sizeImg[1]);
                imagepng($newImage, $this->file['tmp_name']);
                break;
            case 'gif':
                $image = imagecreatefromgif($this->file['tmp_name']);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $sizeImg[0], $sizeImg[1]);
                imagegif($newImage, $this->file['tmp_name']);
                break;
            case 'jpg':
                $image = imagecreatefromjpeg($this->file['tmp_name']);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $sizeImg[0], $sizeImg[1]);
                imagejpeg($newImage, $this->file['tmp_name']);
                break;
            case 'jpeg':
                $image = imagecreatefromjpeg($this->file['tmp_name']);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $sizeImg[0], $sizeImg[1]);
                imagejpeg($newImage, $this->file['tmp_name']);
                break;
        }
    }

    /**
     * Method extension
     * Obtêm a extênsão do arquivo
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access private
     */
    private function extension()
    {
        $this->extension = strtolower(end(explode('.', $this->file['name'])));
    }

    /**
     * Method save
     * Move o arquivo para o novo diretório e atribui um nome ao arquivo
     * @param string $dir
     * @param null|string $name
     * @return string
     */
    public function save($dir, $name = null)
    {
        if (is_null($name)) {
            $this->fileName = md5(uniqid(rand(), true)) . '.' . $this->extension;
        } else {
            $this->fileName = "$name.{$this->extension}";
        }

        if (!preg_match('[\\\\|\/$/]', $dir)) {
            $dir .= '/';
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }

        move_uploaded_file($this->file['tmp_name'], $dir . $this->fileName);
        return $this->fileName;
    }
}
