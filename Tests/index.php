<?php
include '../App.class.php';
include '../Route.class.php';

echo 'Ok'; die;

$app = new EasyFast\App;
$app->setConfigFile('config.ini');
$app->setConfigFileRoute('routes.ef');
$app->setDir(__DIR__);
$app->run();

