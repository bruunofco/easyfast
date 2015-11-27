<?php
$phar = new Phar('../easyfast.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "easyfast.phar");

$phar->startBuffering();
$phar->setStub('#!/usr/bin/php
<?php
Phar::mapPhar();
include "phar://easyfast.phar/bootstrap.php"; __HALT_COMPILER();
');
$phar->buildFromDirectory('./');
$phar->stopBuffering();

