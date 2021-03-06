<?php
function __autoload($classname) {
    include(dirname(__FILE__)."/channels/$classname.class.php");
}
class OAuthException extends Exception {
}
abstract class AbstractSocialChannel
{
    protected static $boundary = "";
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $accessToken = NULL;
    protected $refreshToken = NULL;
    protected $http_code;
    protected $host;

    abstract function getServiceName();
    abstract protected function accessTokenURL();
    abstract protected function authorizeURL();
    public function __construct($config, $accessToken = NULL, $refreshToken = NULL){

    }

    protected function get($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        return json_decode($response, true);
    }

    protected function post($url, $parameters = array(), $multi = false) {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi );
        return json_decode($response, true);
    }

    protected function delete($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        return json_decode($response, true);
    }

    protected function oAuthRequest($url, $method, $parameters, $multi = false) {

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}";
        }

        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->httpRequest($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->httpRequest($url, $method, $body, $headers);
        }
    }

    protected function httpRequest($url, $method, $postfields = NULL, $headers = array()) {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, 'Social Plugin v1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, '20');
        curl_setopt($ci, CURLOPT_TIMEOUT,'30');
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        if ( isset($this->accessToken) && $this->accessToken )
            $headers[] = "Authorization: OAuth2 ".$this->accessToken;

        //$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        curl_close ($ci);
        return $response;
    }

    protected static function build_http_query_multi($params) {
        if (!$params) return '';

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {

            if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
                $url = ltrim( $value, '@' );
                $content = file_get_contents( $url );
                $array = explode( '?', basename( $url ) );
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content. "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value."\r\n";
            }

        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

    public function setAccessToken($token){
        $this->accessToken = $token;
    }

    public function getAccessToken( $type = 'code', $keys ) {
        $params = array();
        $params['client_id'] = $this->clientId;
        $params['client_secret'] = $this->clientSecret;
        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $this->redirectUri;
        } elseif ( $type === 'password' ) {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            throw new OAuthException("wrong auth type");
        }

        $response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
        $token = json_decode($response, true);
        if ( is_array($token) && !isset($token['error']) ) {
            $this->accessToken = $token['access_token'];
            $this->refreshToken = $token['refresh_token'];
        } else {
            throw new OAuthException("get access token failed." . $token['error']);
        }
        return $token;
    }

    public function getAuthorizeUrl($responseType='code', $state=NULL, $display = NULL)
    {
        $params = array(
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => $responseType,
            'state' => $state,
            'display' => $display
        );
        return $this->authorizeURL() . "?" . http_build_query($params);
    }
}
?>