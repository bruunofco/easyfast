<?php
include __DIR__ . '/Init.class.php';

$init = new \EasyFast\Bash\Init();
$init->getArgAction($argv, $argc);