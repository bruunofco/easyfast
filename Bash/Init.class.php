<?php
namespace EasyFast\Bash;

include __DIR__ . '/ParentBash.class.php';
include __DIR__ . '/Method.class.php';
include __DIR__ . '/GenerateClass.class.php';
include __DIR__ . '/Htaccess.class.php';
include __DIR__ . '/Config.class.php';
include __DIR__ . '/Project.class.php';

class Init
{
    protected $dir;

    /**
     * @var array
     */
    public $argsAction = array(
        'class' => '\EasyFast\Bash\GenerateClass',
        'method' => '\EasyFast\Bash\Method',
        'htaccess' => '\EasyFast\Bash\Htaccess',
        'config' => '\EasyFast\Bash\Config',
        'project' => '\EasyFast\Bash\Project'
    );

    /**
     * @param $argv
     */
    public function getArgAction($argv, $argc)
    {
        $this->checkArgs($argv);
        if ($argv[1] != 'create') {
            echo "Error method {$argv[1]} does not exist" . PHP_EOL;
            exit();
        }

        if (array_key_exists($argv[2], $this->argsAction)) {
            $class = $this->argsAction[$argv[2]];
            $class = new $class;
            $class->init();
        }
    }

    /**
     * set directory
     * @param $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * check exists arguments
     * @param $argv
     */
    public function checkArgs($argv)
    {
        if (!isset($argv[1])) {
            echo 'Invalid argument. try help.' . PHP_EOL;
            exit();
        }
    }
}