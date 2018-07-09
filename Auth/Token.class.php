<?php
namespace EasyFast\Auth;

/**
 * Class Token
 * @package EasyFast\Auth
 */
class Token
{
    private $privateKey = '5bb4c7b';

    private $salt = 2;

    private $timeout = 3600;

    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    private function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param null $data
     * @return string
     */
    public function createToken($data = null)
    {
        $pos = rand(0, 9);

        $date = new \DateTime();
        $dateNow = $date->getTimestamp();
        $date->add(new \DateInterval('PT1H'));

        $token = array(
            'key' => $this->getPrivateKey(),
            'create' => $dateNow,
            'timeout' => $date->getTimestamp(),
            'data' => $data
        );

        $b6 = base64_encode(json_encode($token));
        $token = $pos . substr($b6, 0, $pos + 1) . rand($pos) . substr($b6, $pos + 1);
        return $token;
    }

    public function checkToken($token)
    {
        $pos = (int)substr($token, 0, 1) + 1;
        $token1 = substr($token, 1, $pos);
        $token2 = substr($token, $pos + 1);




        $token = json_decode(base64_decode($token));

        $token['key'] = 1;

    }
}
