<?php
require_once(dirname(__FILE__).'/../common/Cryptography.class.php');
class SocialClient
{
    private $crypto;
    private $payload;
    private $action;
    private $callback_link;

    public function __construct($config){
        $this->crypto = new Cryptography($config['publicKey']);
        $this->callback_link = $config['callback_link'];
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

    public function actionAuthorize(){
        $this->action = 'authorize';
        $data = array(
            'channel' => $_POST['channel'],
            'unique_id' => '1234567890',
            'client_type' => 'mobile',
            'callback_url' => $this->callback_link,
        );
        $serialized_data = json_encode($data);
        $this->payload = $this->crypto->encrypt($serialized_data);
    }

    public function actionShare(){
        $this->action = 'share';
        $data = array(
            'channel' => join(',',$_POST['channel']),
            'unique_id' => '1234567890',
            'text' => $_POST['text'],
            'img_url' => $_POST['img_url'],
            'link_url' => $_POST['link_url'],
        );
        $serialized_data = json_encode($data);
        $this->payload = $this->crypto->encrypt($serialized_data);
    }

    public function getPayload(){
        return $this->payload;
    }

    public function getAction(){
        return $this->action;
    }
}
