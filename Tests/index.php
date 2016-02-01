<?php
include '../App.class.php';

try {
    $app = new EasyFast\App();
    $app->setConfigFile('config.ini');
    $app->setConfigFileRoute('routes.ef');
    $app->run();
} catch (\EasyFast\Exceptions\EasyFastException $e) {
    echo 'Ocorreu um erro: ' . $e->getMessage();
}
