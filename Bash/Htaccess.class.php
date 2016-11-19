<?php
namespace EasyFast\Bash;

/**
 * Class Htaccess
 * Create htaccess class
 * @package EasyFast\Bash
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Htaccess extends ParentBash
{
    protected $name;

    protected $controller;

    public $dir;

    protected $author;

    protected $fileName;

    protected $namespace;

    /**
     * init
     * Start actions and assigns values
     */
    public function init()
    {
        if (is_null($this->dir)) {
            $this->fileName = getcwd() . '/.htaccess';
        } else {
            $this->fileName = $this->dir . '/.htaccess';
        }

        $this->createHtaccess();
    }

    /**
     * createMethod
     * Create directore e file
     * @return void
     */
    public function createHtaccess()
    {
        if (file_exists("{$this->fileName}")) {
            echo ".htaccess already exists \n";
            exit();
        }

        file_put_contents($this->fileName, $this->getStructure());
        echo "success \n";
    }


    /**
     * getStructure
     * Returns method structure
     * @return string
     */
    public function getStructure()
    {
        return <<<file
# Generated with EasyFast PHP Framework
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !favicon.ico$
  RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

  <Files *.ini>
    Order allow,deny
    Deny from all
  </Files>
  <Files *.xml>
    Order allow,deny
    Deny from all
  </Files>
</IfModule>
file;
    }
}
