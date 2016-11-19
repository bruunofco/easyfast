<?php
include __DIR__ . '/../../App.class.php';
$app = new EasyFast\App();

class Token_Test
{
    public function gerarToken()
    {
        $token = new EasyFast\Auth\Token();
        $token = $token->createToken();

        return $token;
    }

    public function decodificarToken($tokenR)
    {
        $token = new EasyFast\Auth\Token();
        $token->checkToken($tokenR);
    }
}

$ws = new Token_Test();
$ws->decodificarToken($ws->gerarToken());