<?php
namespace EasyFast\Bash;

/**
 * Class Method
 * Create method class
 * @package EasyFast\Bash
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Method extends ParentBash
{
    public $name;

    public $controller;

    public $dir;

    public $author;


    /**
     * init
     * Start actions and assigns values
     */
    public function init()
    {
        echo "Create method \n";
        $this->name = $this->readStdin('Name: ', null);
        $this->controller = $this->readStdin('Controller (name including directory Ex: Controller/Home): ', null);
        $this->author = $this->readStdin('Author: ', null);
        $this->createFile();
        echo "Successfully created method \n";

        $continue = $this->readStdin('Add more methods? (yes, no) ', array('yes', 'no', 'y', 'n'));
        while ($continue == 'yes' OR $continue == 'y') {
            $this->name = $this->readStdin('Name: ', null);
            $this->createFile();
            echo "Successfully created method \n";
            $continue = $this->readStdin('Add more methods? (yes, no) ', array('yes', 'no', 'y', 'n'));
        }
    }

    /**
     * createMethod
     * Create directore e file
     * @return void
     */
    public function createFile()
    {
        $filename = getcwd() . "/{$this->controller}.class.php";

        if (!file_exists($filename)) {
            echo "Controller not exists \n";
            exit();
        }

        $class = substr(trim(file_get_contents($filename)), 0, -1);

        if (strpos($class, "public function {$this->name}")) {
            echo "Method already exists \n";
            exit();
        }

        $class .= $this->getStructure();
        unlink($filename);
        file_put_contents($filename, $class);
    }


    /**
     * getStructure
     * Returns method structure
     * @return string
     */
    public function getStructure()
    {
        return <<<file
    /**
     * {$this->name}
     * @generator EasyFast PHP Framework
     * @author {$this->author}
     */
    public function {$this->name}()
    {
    }

}
file;
    }
}
