<?php
namespace EasyFast\Http;

/**
 * Class WebSocket
 *
 * @package EasyFast\Http
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
abstract class WebSocket
{
    /**
     * Host WebSocket
     *
     * @var string
     */
    private $host;

    /**
     * Port WebSocket
     *
     * @var string
     */
    private $port;

    /**
     * Limit connections
     *
     * @var int
     */
    private $limit;

    /**
     * Socket
     *
     * @var resource
     */
    private $socket;

    /**
     * Clients
     *
     * @var array
     */
    private $clients;

    /**
     * Name Script
     *
     * @var string
     */
    private $script;

    /**
     * WebSocket constructor.
     *
     * @param $host
     * @param $port
     * @param int $limit
     * @param null $script
     */
    public function __construct($host, $port, $limit = 100, $script = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->limit = $limit;
        $this->script = $script;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->host, $this->port);
        socket_listen($this->socket, 10);
        $this->clients[] = $this->socket;
    }

    /**
     * onConnect
     * @param $client
     * @param $ip
     * @return mixed
     */
    abstract public function onConnect($client, $ip);

    /**
     * onMessage
     *
     * @param $client
     * @param $message
     * @return mixed
     */
    abstract public function onMessage($client, $message);

    /**
     * onClose
     *
     * @param $client
     * @return mixed
     */
    abstract public function onClose($client);

    /**
     * Run web socket
     */
    public function run()
    {
        while (true) {
            $changed = $this->clients;
            socket_select($changed, $write = null, $except = null, null);

            if (in_array($this->socket, $changed)) {
                $newClient = socket_accept($this->socket);
                $this->clients[] = $newClient;
                $header = socket_read($newClient, 1024);
                $this->handshaking($header, $newClient);
                socket_getpeername($newClient, $ip);
                // OnConenect
                $this->onConnect($newClient, $ip);
                $foundSocket = array_search($this->socket, $changed);
                unset($changed[$foundSocket]);
            }

            foreach ($changed as $changedSocket) {
                $bytes = @socket_recv($changedSocket, $buf, 4096, 0);

                if (!$bytes) {
                    socket_getpeername($changedSocket, $ip);
                    $this->clientDisconnect($changedSocket);
                    $this->onClose($changedSocket);
                } else {
                    while ($bytes >= 1) {
                        $receiveMessage = $this->unmask($buf);
                        $dataMessage = json_decode($receiveMessage);
                        if (json_last_error() == JSON_ERROR_NONE) {
                            $receiveMessage = $dataMessage;
                        }
                        // OnMessage
                        $this->onMessage($changedSocket, $receiveMessage);
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * handshaking
     *
     * @param $receved_header
     * @param $client_conn
     */
    private function handshaking($receved_header, $client_conn)
    {
        $headers = array();
        $lines = preg_split("/\r\n/", $receved_header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $this->host\r\n" .
            "WebSocket-Location: ws://$this->host:$this->port/{$this->script}\r\n" .
            "Sec-WebSocket-Accept: $secAccept\r\n\r\n";
        socket_write($client_conn, $upgrade, strlen($upgrade));
    }

    /**
     * Mask messages
     *
     * @param $text
     * @return string
     */
    protected function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        $header = null;

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        return $header . $text;
    }

    /**
     * Unmask messages
     *
     * @param $text
     * @return null|string
     */
    protected function unmask($text)
    {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = null;
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    /**
     * Send Message clients
     *
     * @param $message
     * @param null $client
     * @param bool|true $mask
     * @return bool
     */
    public function sendMessage($message, $client = null, $mask = true)
    {
        if ($mask) {
            $dataMessage = json_encode($message);
            if (json_last_error() == JSON_ERROR_NONE) {
                $message = $dataMessage;
            }
            $message = $this->mask($message);
        }
        if (is_null($client)) {
            foreach ($this->clients as $changedSocket) {
                @socket_write($changedSocket, $message, strlen($message));
            }
        } else {
            @socket_write($client, $message, strlen($message));
        }

        return true;
    }

    /**
     * Client disconnect
     *
     * @param $socket
     */
    protected function clientDisconnect($socket)
    {
        $key = array_search($socket, $this->clients);
        unset($this->clients[$key]);
    }

    /**
     * Disconnect client
     *
     * @param $socket
     */
    public function disconnect($socket)
    {
        $this->clientDisconnect($socket);
        socket_close($socket);
    }
}
