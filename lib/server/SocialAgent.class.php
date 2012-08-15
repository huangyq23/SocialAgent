<?php
require_once(dirname(__FILE__).'/../common/Cryptography.class.php');
require_once(dirname(__FILE__).'/SocialChannel.class.php');

class SocialAgent{
    /**
     * @var Cryptography
     */
    private $cryptography;
    /**
     * @var PDO
     */
    private $dbConnection;
    private $dbConfig;
    private $channels;

    private function getDbConnection(){
        if(!$this->dbConnection){
            $this->dbConnection = new PDO('mysql:host='.$this->dbConfig['host'].';dbname='.$this->dbConfig['dbname'], $this->dbConfig['user'], $this->dbConfig['passoword']);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->dbConnection;
    }

    public function __construct($config){
        if (!session_id()) {
            session_start();
        }
        $this->cryptography = new Cryptography($config['publicKey'], $config['privateKey']);
        $this->dbConfig = $config['dbConfig'];
        $this->channels= $config['channels'];
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
        $error = $_GET['error'];
        $error_description = $_GET['error_description'];
        if(!empty($error)){
            echo 'Failed: '.htmlentities($error_description);
        }else{
            $state = $_GET['state'];
            if(array_key_exists($state, $_SESSION)){
                $state_data =  $_SESSION[$state];
                $code = $_GET['code'];
                $params = array(
                    'code' => $code
                );
                $socialChannel = $this->getChannel($channel);
                $accessToken = $socialChannel->getAccessToken('code', $params);
                try{
                    $this->updateAccessToken($state_data['unique_id'], $state_data['channel'], $accessToken['access_token'], $accessToken['expires_in'], $accessToken['uid']);
                    if(array_key_exists('callback_url', $state_data)){
                        $this->redirect($state_data['callback_url']);
                    }else{
                        echo 'Success';
                    }
                }catch (PDOException $e){
                    echo 'Failed: Database Error';
                }
            }
        }
    }

    private function decodePayload($payload){
        $json = $this->cryptography->decrypt($payload);
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
        if (array_key_exists($channel, $this->channels)){
            $channelData = $this->channels[$channel];
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
        $text= $data['text'];
        $linkUrl= $data['link_url'];
        $accessToken = $this->findAccessToken($uniqueId, $channel);
        if($accessToken){
            $socialChannel = $this->getChannel($channel);
            $socialChannel->setAccessToken($accessToken);
            $result = $socialChannel->shareWithImage($text.'|'.$linkUrl, $data['img_url']);
            echo $socialChannel->getPermlinkById($result['user']['id'], $result['id']);
        }else{
            echo "ERROR! Cannot Find Access Token";
        }
    }
}
?>