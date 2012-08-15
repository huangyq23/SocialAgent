<?php
require_once(dirname(__FILE__).'/../common/Cryptography.class.php');
class SocialClient
{
    private $crypto;
    private $payload;
    private $action;
    private $callbackUrl;

    public function __construct($config){
        $this->crypto = new Cryptography($config['publicKey']);
        $this->callbackUrl = $config['callbackUrl'];
    }

    public function handleClientRequest(){
        $action=$_POST['action'];
        if(empty($action)){
            $action='index';
        }
        $action = ucfirst(strtolower($action));
        if(method_exists($this, 'action'.$action)){
            $this->{'action'.$action}();
        }
    }

    public function actionIndex(){
        echo "12";
    }

    private function authorize($unique_id, $channel, $client_type='default'){
        $this->action = 'authorize';
        $data = array(
            'channel' => $channel,
            'unique_id' => $unique_id,
            'client_type' => $client_type,
            'callback_url' => $this->callbackUrl,
        );
        $serialized_data = json_encode($data);
        $this->payload = $this->crypto->encrypt($serialized_data);
    }

    public function actionAuthorize(){
        $this->authorize('1234567890', $_POST['channel'], 'mobile');
    }

    private function share($unique_id, $channels, $text, $img_url=NULL, $link_url=NULL){
        $this->action = 'share';
        $data = array(
            'channels' => $channels,
            'unique_id' => $unique_id,
            'text' => $text,
            'img_url' => $img_url,
            'link_url' => $link_url,
        );
        $serialized_data = json_encode($data);
        $this->payload = $this->crypto->encrypt($serialized_data);
    }

    public function actionShare(){
        $this->share('1234567890', $_POST['channels'], $_POST['text'], $_POST['img_url'], $_POST['link_url']);
    }

    public function getPayload(){
        return $this->payload;
    }

    public function getAction(){
        return $this->action;
    }
}
