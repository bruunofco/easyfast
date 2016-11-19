<?php
namespace EasyFast\Bash;

/**
 * Class Config
 * Create config class
 * @package EasyFast\Bash
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Config extends ParentBash
{
    protected $app = array();

    protected $database = array();

    protected $view = array();

    protected $fileName = 'easyfast_conf.ini';

    /**
     * init
     * Start actions and assigns values
     */
    public function init()
    {
        echo "Insert initial application settings \n";
        $this->app['name'] = $this->readStdin('Aplication name: ', null);
        $this->app['webhost'] = $this->readStdin('WebHost (http://www.example.com): ', null);
        $this->app['defaultController'] = $this->readStdin('Default controller (Enter to null or Controller class): ', null);

        $configDataBase = $this->readStdin('Config database? (yes or no): ', array('yes', 'no', 'y', 'n'));

        if ($configDataBase == 'yes' OR $configDataBase == 'y') {
            echo "Insert config database \n";
            $this->database['hostname'] = $this->readStdin('Server hostname (mysql.example.com): ', null);
            $this->database['dbname'] = $this->readStdin('Database name: ', null);
            $this->database['username'] = $this->readStdin('Username: ', null);
            $this->database['password'] = $this->readStdin('Password: ', null);
            $this->database['drive'] = $this->readStdin('Drive (mysql): ', null, 'mysql');
            $this->database['port'] = $this->readStdin('Port (3306): ', null, '3306');
        }

        $configView = $this->readStdin('Config view? (yes or no): ', array('yes', 'no', 'y', 'n'));

        if ($configView == 'yes' OR $configView == 'y') {
            echo "Insert config view \n";
            $this->view['title'] = $this->readStdin('Title (My Site): ', null);
            $this->view['dirTpl'] = $this->readStdin('Directory TPL: ', null);
        }

        $this->createFile();
    }

    /**
     * createFile
     * Create directore e file
     * @return void
     */
    public function createFile()
    {
        if (file_exists(getcwd() . "/{$this->fileName}")) {
            echo "File Config already exists \n";
            exit();
        }

        file_put_contents(getcwd() . "/{$this->fileName}", $this->getStructure());
    }

    /**
     * getStructure
     * Returns class structure
     * @return string
     */
    public function getStructure()
    {
        return <<<file
; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ;
; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ;
; Generated with EasyFast PHP Framework         ;
; @github https://github.com/bruunofco/easyfast ;
; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ; ;

; Application initial config
[App]
Name                            = {$this->app['name']}
WebHost                         = {$this->app['webhost']}
DefaultController               = {$this->app['defaultController']}
RouteAutomatic                  = true
ControllerDirectory             = Controller

; Data Base config
[DataBase]
Main[HostName]                  = {$this->database['hostname']}
Main[DBName]                    = {$this->database['dbname']}
Main[UserName]                  = {$this->database['username']}
Main[Password]                  = {$this->database['password']}
Main[Drive]                     = {$this->database['drive']}
Main[Port]                      = {$this->database['port']}

; Variables available in the View
[View]
Title                           = {$this->view['title']}
DirTpl                          = {$this->view['dirTpl']}

file;
    }
}
