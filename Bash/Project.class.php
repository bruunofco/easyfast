<?php
namespace EasyFast\Bash;

/**
 * Class Project
 * @package EasyFast\Bash
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Project extends ParentBash
{
    const URL_EASYFAST = 'https://github.com/bruunofco/easyfast/archive/master.zip';

    public $name;

    public $directory;

    /**
     * init
     * Start actions and assigns values
     */
    public function init()
    {
        echo 'Welcome EasyFast Framework' . PHP_EOL;
        $this->directory = $this->readStdin('Change Directory for installation? (' . getcwd() . '): ', null, getcwd());
        $this->name = $this->readStdin('Project Name: ', null);
        $download = $this->readStdin('Download EasyFast Framework (yes, no): ', array('yes', 'y', 'no', 'n'));

        if ($download == 'yes' OR $download == 'y') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, static::URL_EASYFAST);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this, 'progressDownload'));
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec ($ch);
            $error = curl_error($ch);
            curl_close ($ch);

            @file_put_contents("{$this->directory}/easyfast.zip", $data);
            echo $error . PHP_EOL;
            exit();
        }
        echo 'Download success!' . PHP_EOL;
        $zip = new \ZipArchive();
        $zip->open("{$this->directory}/easyfast.zip");
        if (!$zip->extractTo("{$this->directory}/easyfast")) {
            echo 'Error while unpacking.' . PHP_EOL;
        }
        $zip->close();

        // Create Htaccess
        $htaccess = new Htaccess();
        $htaccess->dir = $this->directory;
        $htaccess->createHtaccess();

        $config = $this->readStdin('Create config file (yes, no): ', array('yes', 'y', 'no', 'n'));

        if ($config == 'yes' OR $config == 'y') {
            
        }

    }

    /**
     * progressDownload
     * @param $resource
     * @param $download_size
     * @param $downloaded
     * @param $upload_size
     * @param $uploaded
     */
    function progressDownload ($resource, $download_size, $downloaded, $upload_size, $uploaded)
    {
        if ($download_size > 0) {
            echo 'Downloading' . PHP_EOL;
            $i = ($downloaded / $download_size  * 100);
            echo "\033[5D";
            echo str_pad($i, 3, ' ', STR_PAD_LEFT) . "%";
            sleep(1);
        }
    }
}