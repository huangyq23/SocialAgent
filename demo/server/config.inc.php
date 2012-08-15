<?php
$server_config = array();

//Datebase config
$server_config['dbConfig'] = array(
    'host' => 'localhost',
    'dbname' => 'social_agent',
    'user' => 'social_agent',
    'password' => '21890*(H&^&Y)(90324',
);

//Channel config
$server_config['channels'] = array(
    'weibo' => array(
        'className'=>'WeiboSocialChannel',
        'config'=>array(
            'WB_HOST' => 'https://api.weibo.com/2/',
            'WB_AKEY' => '',//Key
            'WB_SKEY' => '',//Secret
            'WB_CALLBACK_URL' => 'http://localhost/social/demo/server/weibo_endpoint.php',
        ),
    ),
);

//Security config (public and private Key)
$server_config['publicKey']= <<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhHYgpBmCEJIWJIoAkPvJRs1w4
xlltfKKbzaRgU2gVVtH/kMFePotjGkSlONcb9k4Bu+zAjE2LEg2GpfUcjrexqA7H
oLe9XD9/X1tDloXHNe+7G00VxGviStZkYuooVjE7OQ+1WMnSIJMKYAyfuz9sBwmN
VBZK4K6HvXfGIp+u8wIDAQAB
-----END PUBLIC KEY-----
EOF;

$server_config['privateKey'] = <<<EOF
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

?>