<?php
require_once('SocialChannel.class.php');
require_once('Cryptography.class.php');

class SocialAgent{
    private static $publicKey = <<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4
xlltfKKbzaRgU2gVVtH/kMFePotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7H
oLe9XD9/X1tDloXHNe+7G00VxGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmN
VBZK4K6HvXfGIp+u8wIDAQAB
-----END PUBLIC KEY-----
EOF;

    private static $privateKey = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4xlltfKKbzaRgU2gVVtH/kMFe
PotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7HoLe9XD9/X1tDloXHNe+7G00V
xGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmNVBZK4K6HvXfGIp+u8wIDAQAB
AoGBAODLwMBW4eKTJdX/Yb7QLOJxHVKEn6C5qRe0jsSyBWnpvOJcBvy5sC9Sd+IV
lJkTqGoK4yyT7otFh8RBzTarPbqGp+FA0w1yooGtgBu2wgahDeQOvZOP7HB0mUZo
DuJQeaQiH8PS/m0EaEZOCH3rhYb6TvDmYBKGaJs9Q3ocw8VBAkEA+cxpDUv4w91o
9LQ3fFMqc4l1tTBQg69LQ6Ra+UORSnVZsMJE5F5RgXsuTdbiqNKbwtwQqS5w1aKJ
PVpfgMdaCQJBAOa0P5eU9SAi62cwBBx1r3oLv8Fz9kjbgcKDn7pWS9FW8KX4M9/j
3959WdUY+siW1N3+2LKpqsuWDnf7wyrbsBsCQCeWP2e+DHRt2D4/eTOYsneQ5ziJ
qZjU5OaZW1l5XcMhCc+7WdOfJueQL+xiC5WZmtmsqm9FTthsY7d3ZP8xmJECQQDO
x957ygqPvFzMh1AYBi+7L463IW4tTXoX04w2IyUfxFI8IKS2V3QP4sDC7PnTEsZH
GCY4tTSd96iOSH1dC73jAkAozihPZ5dwQENa8D2/l29apPaOaZE7j3vQCGuIr8xP
fN/RK+ipFvCzjMQVfZ1KmMpZGZ9X9gugNE1ABc1/eMdS
-----END RSA PRIVATE KEY-----
EOF;

    private $crypto;

    private static $channels = array(
        'weibo' => array(
            'className'=>'WeiboSocialChannel',
            'config'=>array(
                'WB_HOST' => 'https://api.weibo.com/2/',
                'WB_AKEY' => '835009534',
                'WB_SKEY' => 'f6902a3bbbf50320d283598f7fcd15c3',
                'WB_CALLBACK_URL' => 'http://localhost/social/weibo/endpoint.php',
            ),
        ),
    );

    public function __construct(){
        $this->crypto = new Cryptography(self::$publicKey, self::$privateKey);
    }

	public function handleClientRequest(){
    	$action=$_GET['action'];
    	if(empty($action)){
    		$action='index';
    	}
        $action = ucfirst(strtolower($action));
    	if(method_exists($this, 'action'.$action)){
    		$this->{'action'.$action}();
    	}
    }

    public function handleOAuthEndPoint($channel){
        $code = $_GET['code'];
        $params = array(
            'code' => $code
        );
        $socialChannel = $this->getChannel($channel);
        $accessToken = $socialChannel->getAccessToken('code', $params);
        print_r($accessToken);
    }

    private function decodePayload($payload){
        $json = $this->crypto->decrypt($payload);
        return json_decode($json, TRUE);
    }
    public static function redirect($url, $statusCode = 302){
        header('Location: ' . $url, true, $statusCode);
    }

    private function findAccessToken($unique_id, $channel){
        return '2.00KEmJDC0iHcVu4646c7ea377MdiaB';
    }

    private function getChannel($channel){
        if (array_key_exists($channel, self::$channels)){
            $channelData = self::$channels[$channel];
            $className = $channelData['className'];
            $socialChannel = new $className($channelData['config']);
            return $socialChannel;
        }
        return FALSE;
    }

    public function actionIndex(){
        echo "12";
    }

    public function actionAuthorize(){
        $data = $this->decodePayload($_POST['payload']);
        $channel = $data['channel'];
        $uniqueId = $data['unique_id'];
        $clientType = $data['client_type'];
        $socialChannel = $this->getChannel($channel);
        $authorizeUrl = $socialChannel->getAuthorizeUrl('code', '', $clientType);
        self::redirect($authorizeUrl);
    }

    public function actionShare(){
    	$data = $this->decodePayload($_POST['payload']);
        $channel = $data['channel'];
        $uniqueId = $data['unique_id'];
        $accessToken = $this->findAccessToken($uniqueId, $channel);
        $socialChannel = $this->getChannel($channel);
        $socialChannel->setAccessToken($accessToken);
        $result = $socialChannel->shareWithImage($data['text'], $data['img_url']);
        print_r($result);
        echo $socialChannel->getPermlinkById($result['user']['id'], $result['id']);
    }
}
?>