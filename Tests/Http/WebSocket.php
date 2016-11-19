<?php
include __DIR__ . '/../../App.class.php';
include __DIR__ . '/../../Http/WebSocket.class.php';

class WebSocket_Test extends EasyFast\Http\WebSocket
{
    public function onConnect($client, $ip)
    {
        $this->sendMessage('Seja bem vindo', $client);
    }

    public function onMessage($client, $message)
    {
        $this->sendMessage('Oii respostas.', $client);
    }

    public function onClose($client)
    {
        echo 'Disconnect';
    }
}

$ws = new WebSocket_Test('localhost', 4321, 100, 'WebSocket.php');
$ws->run();


