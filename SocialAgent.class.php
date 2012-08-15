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
    private $dbConnection;

    private static $dbConfig = array(
        'host' => 'localhost',
        'dbname' => 'social_agent',
        'user' => 'social_agent',
        'passoword' => '21890*(H&^&Y)(90324',
    );

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

    private function getDbConnection(){
        if(!$this->dbConnection){
            $this->dbConnection = new PDO('mysql:host='.self::$dbConfig['host'].';dbname='.self::$dbConfig['dbname'], self::$dbConfig['user'], self::$dbConfig['passoword']);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->dbConnection;
    }

    public function __construct(){
        if (!session_id()) {
            session_start();
        }
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
        $state = $_GET['state'];
        if(array_key_exists($state, $_SESSION)){
            $state_data =  $_SESSION[$state];
            $code = $_GET['code'];
            $params = array(
                'code' => $code
            );
            $socialChannel = $this->getChannel($channel);
            $accessToken = $socialChannel->getAccessToken('code', $params);
            $result = $this->updateAccessToken($state_data['unique_id'], $state_data['channel'], $accessToken['access_token'], $accessToken['expires_in'], $accessToken['uid']);
            if(array_key_exists('callback_url', $state_data)){
                $this->redirect($state_data['callback_url']);
            }else{
                echo 'Success';
            }
        }

    }

    private function decodePayload($payload){
        $json = $this->crypto->decrypt($payload);
        return json_decode($json, TRUE);
    }
    public static function redirect($url, $statusCode = 302){
        header('Location: ' . $url, true, $statusCode);
    }

    private function updateAccessToken($unique_id, $channel, $access_token=NULL, $expires_in=NULL, $channel_uid= NULL,  $refresh_token=NULL){
        $expires_in = (int) $expires_in;
        $expire_time = time()+$expires_in;
        $dbConnection = $this->getDbConnection();
        $sql = <<<EOF
INSERT INTO `access_token`
    (`unique_id`, `channel`, `channel_uid`, `access_token`, `expire_time`, `refresh_token`)
VALUES
    (:unique_id, :channel, :channel_uid, :access_token, FROM_UNIXTIME(:expire_time), :refresh_token)
ON DUPLICATE KEY UPDATE
    `access_token` = :access_token,
    `channel_uid` = :channel_uid,
    `expire_time` = FROM_UNIXTIME(:expire_time),
    `refresh_token` = :refresh_token
EOF;
        $stmt = $dbConnection->prepare($sql);
        $stmt->bindParam(':unique_id', $unique_id, PDO::PARAM_STR);
        $stmt->bindParam(':channel', $channel, PDO::PARAM_STR);
        $stmt->bindParam(':channel_uid', $channel_uid, PDO::PARAM_STR);
        $stmt->bindParam(':access_token', $access_token, PDO::PARAM_STR);
        $stmt->bindParam(':expire_time', $expire_time, PDO::PARAM_STR);
        $stmt->bindParam(':refresh_token', $refresh_token, PDO::PARAM_STR);
        try{
            $stmt->execute();
            return TRUE;
        }catch (Exception $e){
            return FALSE;
        }
    }

    private function findAccessToken($unique_id, $channel){
        $dbConnection = $this->getDbConnection();
        $stmt = $dbConnection->prepare("SELECT * FROM `access_token` WHERE `unique_id` = :unique_id AND `channel` = :channel ");
        $stmt->bindParam(':unique_id', $unique_id, PDO::PARAM_STR);
        $stmt->bindParam(':channel', $channel, PDO::PARAM_STR);
        if($stmt->execute()){
            $obj = $stmt->fetchObject();
            return $obj->access_token;
        }
        return FALSE;
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
        $callbackUrl = $data['callback_url'];
        $socialChannel = $this->getChannel($channel);
        $state = uniqid();
        $authorizeUrl = $socialChannel->getAuthorizeUrl('code', $state, $clientType);
        $_SESSION[$state] = array(
            'channel' => $channel,
            'unique_id' => $uniqueId,
            'client_type' => $clientType,
            'callback_url' => $callbackUrl,
        );
        self::redirect($authorizeUrl);
    }

    public function actionShare(){
    	$data = $this->decodePayload($_POST['payload']);
        $channel = $data['channel'];
        $uniqueId = $data['unique_id'];
        $accessToken = $this->findAccessToken($uniqueId, $channel);
        if($accessToken){
            $socialChannel = $this->getChannel($channel);
            $socialChannel->setAccessToken($accessToken);
            $result = $socialChannel->shareWithImage($data['text'], $data['img_url']);
            echo $socialChannel->getPermlinkById($result['user']['id'], $result['id']);
        }else{
            echo "ERROR! Cannot Find Access Token";
        }
    }
}
?>