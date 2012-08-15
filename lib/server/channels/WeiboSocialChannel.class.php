<?php
require_once(dirname(__FILE__).'/../SocialChannel.class.php');
class WeiboSocialChannel extends AbstractSocialChannel
{
    public function __construct($config, $accessToken = NULL, $refreshToken = NULL)
    {
        $this->host = $config['WB_HOST'];
        $this->clientId = $config['WB_AKEY'];
        $this->clientSecret = $config['WB_SKEY'];
        $this->redirectUri = $config['WB_CALLBACK_URL'];
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getServiceName()
    {
        return "Weibo";
    }

    protected function accessTokenURL()
    {
        return 'https://api.weibo.com/oauth2/access_token';
    }

    protected function authorizeURL()
    {
        return 'https://api.weibo.com/oauth2/authorize';
    }

    public function shareWithImage( $text, $pic_path, $lat = NULL, $long = NULL )
    {
        $params = array();
        $params['status'] = $text;
        $params['pic'] = '@'.$pic_path;
        if ($lat) {
            $params['lat'] = floatval($lat);
        }
        if ($long) {
            $params['long'] = floatval($long);
        }

        return $this->post( 'statuses/upload.json', $params, true );
    }

    private static function getCodeByMid($mid){
        include_once(dirname(__FILE__).'/../../common/vendor/Base62/Base62.class.php');
        $base62 = new Base62();
        $str = '';
        for ($i = strlen($mid) - 7; $i > -7; $i -=7)
        {
            $offset1 = $i < 0 ? 0 : $i;
            $offset2 = $i + 7;
            $num = substr($mid, $offset1,$offset2-$offset1);
            $num = $base62->convert($num);
            $str = $num .$str;
        }

        return $str;
    }

    public function getPermlinkById($user_id, $mid){
        $base_62_id = self::getCodeByMid($mid);
        return "http://weibo.com/$user_id/$base_62_id";
    }
}
?>
