<?php
namespace EasyFast\Bash;

/**
 * Class GenerateClass
 * Create controller class
 * @package EasyFast\Bash
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class GenerateClass extends ParentBash
{
    protected $name;

    protected $dir;

    protected $author;

    protected $namespace;

    /**
     * init
     * Start actions and assigns values
     */
    public function init()
    {
        echo "Create controller \n";
        $this->name = $this->readStdin('Name: ', null);
        $this->namespace = $this->readStdin('Namespace (Controller): ', null, 'Controller');
        $this->dir = $this->readStdin('Directory (Controller): ', null, 'Controller');
        $this->author = $this->readStdin('Author: ', null);
        $this->createFile();
        echo "Successfully created controller \n";

        $createMethod = $this->readStdin('Create methods? (yes or no): ', array('yes', 'no', 'y', 'n'));
        while ($createMethod == 'yes' OR $createMethod == 'y') {
            $m = new Method();
            $m->controller = "{$this->dir}/{$this->name}";
            $m->author = $this->author;
            $m->name = $this->readStdin('Name: ', null);
            $m->createFile();
            echo "Successfully created method \n";
            $createMethod = $this->readStdin('Continue create methods? (yes, no) ', array('yes', 'no', 'y', 'n'));
        }
    }

    /**
     * createFile
     * Create directore e file
     * @return void
     */
    public function createFile()
    {
        $filename = getcwd() . "/{$this->dir}/{$this->name}.class.php";

        if (file_exists($filename)) {
            echo "Controller already exists \n";
            exit();
        }

        if (!is_dir(getcwd() . "/{$this->dir}")) {
            mkdir(getcwd() . "/{$this->dir}", 0775, true);
        }

        file_put_contents($filename, $this->getStructure());
    }

    /**
     * getStructure
     * Returns class structure
     * @return string
     */
    public function getStructure()
    {
        return <<<Controller
<?php
namespace {$this->namespace};

/**
 * Class {$this->name}
 * @generator EasyFast PHP Framework
 * @author {$this->author}
 */
class {$this->name} extends \EasyFast\Mvc\Controller
{
}
Controller;
    }
}
